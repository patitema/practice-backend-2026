<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * Get all published surveys.
     */
    public function index()
    {
        $publishedStatusId = Status::where('name', 'published')->value('id');
        
        $surveys = Survey::with(['author', 'status'])
            ->where('status', $publishedStatusId)
            ->get();

        return response()->json($surveys);
    }

    /**
     * Get survey details with questions.
     */
    public function show($id)
    {
        $survey = Survey::with(['author', 'status', 'questions.type', 'questions.options'])
            ->findOrFail($id);

        return response()->json($survey);
    }

    /**
     * Create a new survey (draft).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $draftStatusId = Status::where('name', 'draft')->value('id');

        $survey = Survey::create([
            'author_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $draftStatusId,
        ]);

        return response()->json($survey, 201);
    }

    /**
     * Update a survey (draft only).
     */
    public function update(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        if ($survey->status != Status::where('name', 'draft')->value('id')) {
            return response()->json(['error' => 'Only draft surveys can be edited'], 403);
        }

        if ($survey->author_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $survey->update($request->only(['title', 'description']));

        return response()->json($survey);
    }

    /**
     * Publish a survey.
     */
    public function publish($id)
    {
        $survey = Survey::findOrFail($id);

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $publishedStatusId = Status::where('name', 'published')->value('id');

        $survey->update([
            'status' => $publishedStatusId,
            'published_at' => now(),
        ]);

        return response()->json($survey);
    }

    /**
     * Close a survey.
     */
    public function close($id)
    {
        $survey = Survey::findOrFail($id);

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $closedStatusId = Status::where('name', 'closed')->value('id');

        $survey->update([
            'status' => $closedStatusId,
            'closed_at' => now(),
        ]);

        return response()->json($survey);
    }

    /**
     * Delete a survey.
     */
    public function destroy($id)
    {
        $survey = Survey::findOrFail($id);

        if ($survey->author_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $survey->delete();

        return response()->json(null, 204);
    }
}
