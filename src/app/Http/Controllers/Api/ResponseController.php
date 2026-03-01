<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $request->validate([
            'answers' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Create response
            $response = Response::create([
                'survey_id' => $surveyId,
                'respondent_id' => $request->user()->id,
            ]);

            // Process answers
            foreach ($request->answers as $answerData) {
                Answer::create([
                    'response_id' => $response->id,
                    'question_id' => $answerData['question_id'],
                    'option_id' => $answerData['option_id'] ?? null,
                    'text_value' => $answerData['text_value'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to submit answers'], 500);
        }
    }
}
