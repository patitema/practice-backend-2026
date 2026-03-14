<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\Status;
use App\Http\Resources\OptionResource;
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

        // Cannot add options to published or closed survey
        $publishedStatusId = Status::where('name', 'published')->value('id');
        $closedStatusId = Status::where('name', 'closed')->value('id');
        
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Cannot add options to a published survey'], 403);
        }
        
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot add options to a closed survey'], 403);
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

        return new OptionResource($option);
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

        // Cannot edit options in published or closed survey
        $publishedStatusId = Status::where('name', 'published')->value('id');
        $closedStatusId = Status::where('name', 'closed')->value('id');
        
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Cannot edit options in a published survey'], 403);
        }
        
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot edit options in a closed survey'], 403);
        }

        $request->validate([
            'text' => 'sometimes|required|string|max:125',
            'order' => 'sometimes|required|integer',
        ]);

        $option->update($request->only(['text', 'order']));

        return new OptionResource($option);
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

        // Cannot delete options from published or closed survey
        $publishedStatusId = Status::where('name', 'published')->value('id');
        $closedStatusId = Status::where('name', 'closed')->value('id');
        
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Cannot delete options from a published survey'], 403);
        }
        
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot delete options from a closed survey'], 403);
        }

        $option->delete();

        return response()->json(null, 204);
    }
}
