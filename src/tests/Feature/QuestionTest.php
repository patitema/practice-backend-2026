<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Status;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Type;
use Laravel\Sanctum\Sanctum;

class QuestionTest extends TestCase
{

    private function createAuthor(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    /**
     * Test create question.
     */
    public function test_author_can_create_question(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 1,
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/questions", [
            'type' => 1,
            'text' => 'What is your name?',
            'order' => 1,
            'required' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'text' => 'What is your name?',
                'order' => 1,
            ]);

        $this->assertDatabaseHas('questions', [
            'text' => 'What is your name?',
            'survey_id' => $survey->id,
        ]);
    }

    /**
     * Test cannot add question to published survey.
     */
    public function test_cannot_add_question_to_published_survey(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Published Survey',
            'status' => 2,
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/questions", [
            'type' => 1,
            'text' => 'New Question',
            'order' => 1,
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Cannot add questions to a published survey']);
    }

    /**
     * Test update question.
     */
    public function test_author_can_update_question(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 1,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 1,
            'text' => 'Original question',
            'order' => 1,
        ]);

        $response = $this->putJson("/api/questions/{$question->id}", [
            'text' => 'Updated question',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['text' => 'Updated question']);

        $this->assertDatabaseHas('questions', ['text' => 'Updated question']);
    }
}