<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Add a question to a survey.
     */
    public function store(Request $request, $surveyId)
    {
        $survey = Survey::findOrFail($surveyId);

        if ($survey->author_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => 'required|integer|exists:types,id',
            'text' => 'required|string|max:255',
            'order' => 'required|integer',
            'required' => 'boolean',
        ]);

        $question = Question::create([
            'survey_id' => $surveyId,
            'type' => $request->type,
            'text' => $request->text,
            'order' => $request->order,
            'required' => $request->boolean('required', false),
        ]);

        return response()->json($question, 201);
    }

    /**
     * Update a question.
     */
    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $survey = $question->survey;

        if ($survey->author_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => 'sometimes|required|integer|exists:types,id',
            'text' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer',
            'required' => 'boolean',
        ]);

        $question->update($request->only(['type', 'text', 'order', 'required']));

        return response()->json($question);
    }

    /**
     * Delete a question.
     */
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $survey = $question->survey;

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $question->delete();

        return response()->json(null, 204);
    }
}
