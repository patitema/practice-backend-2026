<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Status;
use App\Http\Resources\SurveyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * Get all published surveys.
     */
    public function index(Request $request)
    {
        $publishedStatusId = Status::where('name', 'published')->value('id');

        $query = Survey::with(['author', 'status'])
            ->where('status', $publishedStatusId);

        // Filter by status if provided
        if ($request->has('status')) {
            $statusId = Status::where('name', $request->status)->value('id');
            if ($statusId) {
                $query->where('status', $statusId);
            }
        }

        // Sort by created_at (default: newest first)
        $sortDirection = $request->get('sort', 'desc');
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        $query->orderBy('created_at', $sortDirection);

        // Paginate (15 per page)
        $perPage = $request->get('per_page', 15);
        $perPage = min(max((int) $perPage, 1), 50); // Limit between 1 and 50

        $surveys = $query->paginate($perPage);

        return SurveyResource::collection($surveys);
    }

    /**
     * Get survey details with questions.
     */
    public function show($id)
    {
        $survey = Survey::with(['author', 'status', 'questions.type', 'questions.options'])
            ->findOrFail($id);

        return new SurveyResource($survey);
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

        return new SurveyResource($survey);
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

        return new SurveyResource($survey->load('author', 'status'));
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

        // Check if survey has at least one question
        if ($survey->questions()->count() === 0) {
            return response()->json(['error' => 'Cannot publish survey without questions'], 400);
        }

        // Check if survey is already published
        $publishedStatusId = Status::where('name', 'published')->value('id');
        if ($survey->status == $publishedStatusId) {
            return response()->json(['error' => 'Survey is already published'], 400);
        }

        // Check if survey is closed
        $closedStatusId = Status::where('name', 'closed')->value('id');
        if ($survey->status == $closedStatusId) {
            return response()->json(['error' => 'Cannot publish a closed survey'], 400);
        }

        $survey->update([
            'status' => $publishedStatusId,
            'published_at' => now(),
        ]);

        return new SurveyResource($survey->load('author', 'status'));
    }

    /**
     * Close a survey.
     */
    public function close($id)
    {
        $survey = Survey::findOrFail($id);
        $user = request()->user();

        // Check if user is admin or author
        if (!$user->isAdmin() && $survey->author_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $closedStatusId = Status::where('name', 'closed')->value('id');

        $survey->update([
            'status' => $closedStatusId,
            'closed_at' => now(),
        ]);

        return new SurveyResource($survey->load('author', 'status'));
    }

    /**
     * Delete a survey.
     */
    public function destroy($id)
    {
        $survey = Survey::findOrFail($id);
        $user = request()->user();

        // Admin can delete any survey, author can delete only their own
        if (!$user->isAdmin() && $survey->author_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $survey->delete();

        return response()->json(null, 204);
    }
}
