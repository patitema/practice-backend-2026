<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    /**
     * Add an option to a question.
     */
    public function store(Request $request, $questionId)
    {
        $question = Question::findOrFail($questionId);
        $survey = $question->survey;

        if ($survey->author_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'text' => 'required|string|max:125',
            'order' => 'required|integer',
        ]);

        $option = Option::create([
            'question_id' => $questionId,
            'text' => $request->text,
            'order' => $request->order,
        ]);

        return response()->json($option, 201);
    }

    /**
     * Update an option.
     */
    public function update(Request $request, $id)
    {
        $option = Option::findOrFail($id);
        $survey = $option->question->survey;

        if ($survey->author_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'text' => 'sometimes|required|string|max:125',
            'order' => 'sometimes|required|integer',
        ]);

        $option->update($request->only(['text', 'order']));

        return response()->json($option);
    }

    /**
     * Delete an option.
     */
    public function destroy($id)
    {
        $option = Option::findOrFail($id);
        $survey = $option->question->survey;

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $option->delete();

        return response()->json(null, 204);
    }
}
