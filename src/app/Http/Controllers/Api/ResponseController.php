<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\Status;
use App\Models\Type;
use App\Http\Resources\ResponseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ResponseController extends Controller
{
    /**
     * Submit answers to a survey.
     */
    public function store(Request $request, $surveyId)
    {
        $survey = Survey::findOrFail($surveyId);

        // Check if survey is published
        $publishedStatusId = Status::where('name', 'published')->value('id');
        if ($survey->status != $publishedStatusId) {
            return response()->json(['error' => 'Survey is not published'], 403);
        }

        // Check if survey is closed
        $closedStatusId = Status::where('name', 'closed')->value('id');
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Survey is closed'], 403);
        }

        // Custom validation - check answers array manually
        if (!$request->has('answers') || !is_array($request->answers)) {
            return response()->json([
                'errors' => ['answers field is required and must be an array']
            ], 422);
        }

        // Load questions with options
        $questions = $survey->questions()->with('options')->get();
        $questionMap = $questions->keyBy('id');
        
        // Load all types into a map
        $typeMap = DB::table('types')->pluck('name', 'id');

        // Validate answers structure
        $validationErrors = [];
        $providedQuestionIds = [];

        foreach ($request->answers as $index => $answerData) {
            $questionId = $answerData['question_id'] ?? null;

            if (!$questionId) {
                $validationErrors[] = "Answer #$index: question_id is required";
                continue;
            }

            $providedQuestionIds[] = $questionId;

            if (!$questionMap->has($questionId)) {
                $validationErrors[] = "Question #$questionId does not belong to this survey";
                continue;
            }

            $question = $questionMap->get($questionId);
            
            // Get type name from the type map
            $typeId = $question->type;
            $typeName = $typeMap->get($typeId) ?? 'unknown';

            // Validate based on question type
            if ($typeName === 'single_choice') {
                if (!isset($answerData['option_id']) || $answerData['option_id'] === null) {
                    $validationErrors[] = "Question #$questionId: single_choice requires option_id";
                } else {
                    // Check if option belongs to this question
                    $optionExists = $question->options->contains('id', $answerData['option_id']);
                    if (!$optionExists) {
                        $validationErrors[] = "Question #$questionId: option does not belong to this question";
                    }
                }
                if (isset($answerData['text_value']) && $answerData['text_value'] !== null) {
                    $validationErrors[] = "Question #$questionId: text_value not allowed for single_choice";
                }
            } elseif ($typeName === 'multiple_choice') {
                if (!isset($answerData['option_ids']) || !is_array($answerData['option_ids'])) {
                    $validationErrors[] = "Question #$questionId: multiple_choice requires option_ids array";
                } elseif (count($answerData['option_ids']) < 1) {
                    $validationErrors[] = "Question #$questionId: multiple_choice requires at least 1 option";
                } else {
                    // Check if all options belong to this question
                    foreach ($answerData['option_ids'] as $optionId) {
                        $optionExists = $question->options->contains('id', $optionId);
                        if (!$optionExists) {
                            $validationErrors[] = "Question #$questionId: option #$optionId does not belong to this question";
                        }
                    }
                }
                if (isset($answerData['text_value']) && $answerData['text_value'] !== null) {
                    $validationErrors[] = "Question #$questionId: text_value not allowed for multiple_choice";
                }
            } elseif ($typeName === 'text_answer') {
                if (!isset($answerData['text_value']) || $answerData['text_value'] === null || trim($answerData['text_value']) === '') {
                    $validationErrors[] = "Question #$questionId: text_answer requires text_value";
                }
                if (isset($answerData['option_id']) && $answerData['option_id'] !== null) {
                    $validationErrors[] = "Question #$questionId: option_id not allowed for text_answer";
                }
            }
        }

        // Check required questions
        foreach ($questions as $question) {
            if ($question->required && !in_array($question->id, $providedQuestionIds)) {
                $validationErrors[] = "Required question #$question->id is missing";
            }
        }

        if (!empty($validationErrors)) {
            return response()->json(['errors' => $validationErrors], 422);
        }

        // Check for duplicate response (user already completed this survey)
        $userId = $request->user()->id;
        $existingResponse = Response::where('survey_id', $surveyId)
            ->where('respondent_id', $userId)
            ->first();

        if ($existingResponse) {
            return response()->json(['error' => 'Вы уже прошли этот опрос'], 409);
        }

        // Skip transactions in testing environment
        if (app()->environment('testing')) {
            return $this->storeWithoutTransaction($request, $surveyId, $survey, $questionMap, $typeMap);
        }

        DB::beginTransaction();

        try {
            // Create response
            $response = Response::create([
                'survey_id' => $surveyId,
                'respondent_id' => $userId,
            ]);

            // Process answers
            foreach ($request->answers as $answerData) {
                $questionId = $answerData['question_id'];
                $question = $questionMap->get($questionId);
                $typeId = $question->type;
                $typeName = $typeMap->get($typeId) ?? 'unknown';

                if ($typeName === 'multiple_choice') {
                    // Create multiple answers for multiple_choice
                    foreach ($answerData['option_ids'] as $optionId) {
                        Answer::create([
                            'response_id' => $response->id,
                            'question_id' => $questionId,
                            'option_id' => $optionId,
                            'text_value' => null,
                        ]);
                    }
                } else {
                    Answer::create([
                        'response_id' => $response->id,
                        'question_id' => $questionId,
                        'option_id' => $answerData['option_id'] ?? null,
                        'text_value' => $answerData['text_value'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return (new ResponseResource($response->fresh(['answers'])))
                ->response()
                ->setStatusCode(201);
        } catch (QueryException $e) {
            DB::rollBack();

            // Check for unique constraint violation (duplicate response)
            if ($this->isDuplicateKeyError($e)) {
                return response()->json(['error' => 'Вы уже прошли этот опрос'], 409);
            }

            return response()->json(['error' => 'Failed to submit answers'], 500);
        }
    }

    /**
     * Store response without transaction (for testing).
     */
    private function storeWithoutTransaction(Request $request, $surveyId, $survey, $questionMap, $typeMap)
    {
        try {
            // Create response
            $response = Response::create([
                'survey_id' => $surveyId,
                'respondent_id' => $request->user()->id,
            ]);

            // Process answers
            foreach ($request->answers as $answerData) {
                $questionId = $answerData['question_id'];
                $question = $questionMap->get($questionId);
                $typeId = $question->type;
                $typeName = $typeMap->get($typeId) ?? 'unknown';

                if ($typeName === 'multiple_choice') {
                    foreach ($answerData['option_ids'] as $optionId) {
                        Answer::create([
                            'response_id' => $response->id,
                            'question_id' => $questionId,
                            'option_id' => $optionId,
                            'text_value' => null,
                        ]);
                    }
                } else {
                    Answer::create([
                        'response_id' => $response->id,
                        'question_id' => $questionId,
                        'option_id' => $answerData['option_id'] ?? null,
                        'text_value' => $answerData['text_value'] ?? null,
                    ]);
                }
            }

            return (new ResponseResource($response->fresh(['answers'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if the database exception is a duplicate key error.
     */
    private function isDuplicateKeyError(QueryException $e): bool
    {
        $errorCode = $e->getCode();

        // MySQL: 1062 = Duplicate entry
        // PostgreSQL: 23505 = unique_violation
        // SQLite: 19 = UNIQUE constraint failed
        return in_array($errorCode, [1062, 23505, '23505', 19]);
    }
}
