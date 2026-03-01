<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Answer;
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

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalResponses = $survey->responses()->count();

        $questions = $survey->questions()->with(['type', 'options', 'answers'])->get();

        $statistics = [];
        foreach ($questions as $question) {
            $questionStats = [
                'question_id' => $question->id,
                'text' => $question->text,
                'type' => $question->type->name,
                'total_answers' => $question->answers()->count(),
            ];

            if ($question->options->isNotEmpty()) {
                $questionStats['options'] = $question->options->map(function ($option) {
                    return [
                        'option_id' => $option->id,
                        'text' => $option->text,
                        'count' => $option->answers()->count(),
                    ];
                });
            }

            $statistics[] = $questionStats;
        }

        return response()->json([
            'survey' => $survey,
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

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $responses = $survey->responses()
            ->with(['respondent', 'answers.question', 'answers.option'])
            ->get();

        return response()->json([
            'survey' => $survey,
            'responses' => $responses,
        ]);
    }
}
