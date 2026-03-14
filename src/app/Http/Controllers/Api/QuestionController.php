<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Survey;
use App\Models\Status;
use App\Http\Resources\QuestionResource;
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

        // Cannot add questions to published or closed survey
        $publishedStatusId = Status::where('name', 'published')->value('id');
        $closedStatusId = Status::where('name', 'closed')->value('id');
        
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Cannot add questions to a published survey'], 403);
        }
        
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot add questions to a closed survey'], 403);
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

        return new QuestionResource($question->load('type'));
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

        // Cannot edit questions in published or closed survey
        $publishedStatusId = Status::where('name', 'published')->value('id');
        $closedStatusId = Status::where('name', 'closed')->value('id');
        
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Cannot edit questions in a published survey'], 403);
        }
        
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot edit questions in a closed survey'], 403);
        }

        $request->validate([
            'type' => 'sometimes|required|integer|exists:types,id',
            'text' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer',
            'required' => 'boolean',
        ]);

        $question->update($request->only(['type', 'text', 'order', 'required']));

        return new QuestionResource($question->load('type'));
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

        // Cannot delete questions from published or closed survey
        $publishedStatusId = Status::where('name', 'published')->value('id');
        $closedStatusId = Status::where('name', 'closed')->value('id');
        
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Cannot delete questions from a published survey'], 403);
        }
        
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot delete questions from a closed survey'], 403);
        }

        $question->delete();

        return response()->json(null, 204);
    }
}
