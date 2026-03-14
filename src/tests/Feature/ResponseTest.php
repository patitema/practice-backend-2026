<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Option;
use App\Models\Type;
use App\Models\Response as ResponseModel;
use Laravel\Sanctum\Sanctum;

class ResponseTest extends TestCase
{

    private function createAuthor(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    private function createRespondent(): User
    {
        return User::factory()->create(['role' => 2]);
    }

    /**
     * Test submit response to survey.
     */
    public function test_can_submit_response_to_survey(): void
    {
        $author = $this->createAuthor();
        $respondent = $this->createRespondent();
        Sanctum::actingAs($respondent);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 1,
            'text' => 'What is your name?',
            'order' => 1,
        ]);

        $option = Option::create([
            'question_id' => $question->id,
            'text' => 'Option A',
            'order' => 1,
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/respond", [
            'answers' => [
                [
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                ],
            ],
        ]);

        $response->assertStatus(201);
        
        $response->assertJsonStructure([
            'data' => [
                'id',
                'survey_id',
                'answers' => [
                    '*' => [
                        'id',
                        'question_id',
                        'option_id',
                        'text_value',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('responses', ['survey_id' => $survey->id]);
    }

    /**
     * Test cannot submit to unpublished survey.
     */
    public function test_cannot_submit_to_unpublished_survey(): void
    {
        $author = $this->createAuthor();
        $respondent = $this->createRespondent();
        Sanctum::actingAs($respondent);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Draft Survey',
            'status' => 1,
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/respond", [
            'answers' => [],
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Survey is not published']);
    }

    /**
     * Test cannot submit duplicate response.
     */
    public function test_cannot_submit_duplicate_response(): void
    {
        $author = $this->createAuthor();
        $respondent = $this->createRespondent();
        Sanctum::actingAs($respondent);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 1,
            'text' => 'What is your name?',
            'order' => 1,
        ]);

        $option = Option::create([
            'question_id' => $question->id,
            'text' => 'Option A',
            'order' => 1,
        ]);

        // First response
        $this->postJson("/api/surveys/{$survey->id}/respond", [
            'answers' => [
                ['question_id' => $question->id, 'option_id' => $option->id],
            ],
        ]);

        // Second response (should fail)
        $response = $this->postJson("/api/surveys/{$survey->id}/respond", [
            'answers' => [
                ['question_id' => $question->id, 'option_id' => $option->id],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson(['error' => 'Вы уже прошли этот опрос']);
    }

    /**
     * Test validation for required question.
     */
    public function test_validation_fails_for_missing_required_question(): void
    {
        $author = $this->createAuthor();
        $respondent = $this->createRespondent();
        Sanctum::actingAs($respondent);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 3,
            'text' => 'Required question',
            'order' => 1,
            'required' => true,
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/respond", [
            'answers' => [], // Missing required question
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['errors' => ["Required question #{$question->id} is missing"]]);
    }

    /**
     * Test validation for text answer type.
     */
    public function test_validation_requires_text_value_for_text_answer(): void
    {
        $author = $this->createAuthor();
        $respondent = $this->createRespondent();
        Sanctum::actingAs($respondent);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 3,
            'text' => 'Your comment',
            'order' => 1,
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/respond", [
            'answers' => [
                ['question_id' => $question->id], // Missing text_value
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['errors' => ["Question #{$question->id}: text_answer requires text_value"]]);
    }
}
