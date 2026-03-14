<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Answer;
use App\Http\Resources\SurveyResource;
use App\Http\Resources\ResponseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    /**
     * Get survey statistics.
     */
    public function show($id)
    {
        $survey = Survey::findOrFail($id);
        $user = request()->user();

        // Admin can view any survey, author can view their own
        if (!$user->isAdmin() && $survey->author_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalResponses = $survey->responses()->count();

        $questions = $survey->questions()->with(['options', 'answers'])->get();
        $typeMap = DB::table('types')->pluck('name', 'id');

        $statistics = [];
        foreach ($questions as $question) {
            $questionStats = [
                'question_id' => $question->id,
                'text' => $question->text,
                'type' => $typeMap->get($question->type) ?? 'unknown',
                'total_answers' => $question->answers()->count(),
            ];

            if ($question->options->isNotEmpty()) {
                $totalOptionAnswers = $question->answers()->count();
                
                $questionStats['options'] = $question->options->map(function ($option) use ($totalOptionAnswers) {
                    $count = $option->answers()->count();
                    $percentage = $totalOptionAnswers > 0 
                        ? round(($count / $totalOptionAnswers) * 100, 2) 
                        : 0;
                    
                    return [
                        'option_id' => $option->id,
                        'text' => $option->text,
                        'count' => $count,
                        'percentage' => $percentage,
                    ];
                });
            }

            $statistics[] = $questionStats;
        }

        return response()->json([
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
            ],
            'total_responses' => $totalResponses,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Export survey results to JSON.
     */
    public function export($id)
    {
        $survey = Survey::findOrFail($id);
        $user = request()->user();

        // Admin can view any survey, author can view their own
        if (!$user->isAdmin() && $survey->author_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $responses = $survey->responses()
            ->with(['respondent', 'answers.question', 'answers.option'])
            ->paginate(15);

        return response()->json([
            'survey' => [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
            ],
            'total_responses' => $survey->responses()->count(),
            'responses' => ResponseResource::collection($responses),
        ]);
    }
}
