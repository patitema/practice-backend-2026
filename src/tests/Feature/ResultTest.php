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
use App\Models\Answer;
use Laravel\Sanctum\Sanctum;

class ResultTest extends TestCase
{

    private function createAuthor(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 3]);
    }

    /**
     * Test get survey statistics.
     */
    public function test_author_can_get_survey_statistics(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 1,
            'text' => 'Your choice',
            'order' => 1,
        ]);

        Option::create(['question_id' => $question->id, 'text' => 'Option A', 'order' => 1]);
        Option::create(['question_id' => $question->id, 'text' => 'Option B', 'order' => 2]);

        $response = $this->getJson("/api/surveys/{$survey->id}/results");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'survey',
                'total_responses',
                'statistics' => [
                    '*' => [
                        'question_id',
                        'text',
                        'type',
                        'total_answers',
                        'options',
                    ],
                ],
            ]);
    }

    /**
     * Test statistics includes percentages.
     */
    public function test_statistics_includes_percentages(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 1,
            'text' => 'Your choice',
            'order' => 1,
        ]);

        $option1 = Option::create(['question_id' => $question->id, 'text' => 'Option A', 'order' => 1]);
        $option2 = Option::create(['question_id' => $question->id, 'text' => 'Option B', 'order' => 2]);

        // Create responses with different respondents
        $respondent1 = User::factory()->create(['role' => 2]);
        $respondent2 = User::factory()->create(['role' => 2]);
        $respondent3 = User::factory()->create(['role' => 2]);
        
        $response1 = ResponseModel::create(['survey_id' => $survey->id, 'respondent_id' => $respondent1->id]);
        Answer::create(['response_id' => $response1->id, 'question_id' => $question->id, 'option_id' => $option1->id]);

        $response2 = ResponseModel::create(['survey_id' => $survey->id, 'respondent_id' => $respondent2->id]);
        Answer::create(['response_id' => $response2->id, 'question_id' => $question->id, 'option_id' => $option1->id]);

        $response3 = ResponseModel::create(['survey_id' => $survey->id, 'respondent_id' => $respondent3->id]);
        Answer::create(['response_id' => $response3->id, 'question_id' => $question->id, 'option_id' => $option2->id]);

        $apiResponse = $this->getJson("/api/surveys/{$survey->id}/results");

        $apiResponse->assertStatus(200);
        
        $data = $apiResponse->json();
        $options = $data['statistics'][0]['options'];
        
        // Option A: 2 out of 3 = 66.67%
        // Option B: 1 out of 3 = 33.33%
        $optionA = collect($options)->firstWhere('option_id', $option1->id);
        $optionB = collect($options)->firstWhere('option_id', $option2->id);

        $this->assertEquals(2, $optionA['count']);
        $this->assertEquals(66.67, $optionA['percentage']);
        $this->assertEquals(1, $optionB['count']);
        $this->assertEquals(33.33, $optionB['percentage']);
    }

    /**
     * Test unauthorized user cannot view results.
     */
    public function test_unauthorized_user_cannot_view_results(): void
    {
        $author = $this->createAuthor();
        $otherUser = $this->createAuthor();
        Sanctum::actingAs($otherUser);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $response = $this->getJson("/api/surveys/{$survey->id}/results");

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);
    }

    /**
     * Test export results.
     */
    public function test_author_can_export_results(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Test Survey',
            'status' => 2,
        ]);

        $response = $this->getJson("/api/surveys/{$survey->id}/results/export");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'survey',
                'total_responses',
                'responses',
            ]);
    }

    /**
     * Test admin can view statistics for any survey.
     */
    public function test_admin_can_view_statistics_for_any_survey(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => "Author's Survey",
            'status' => 2,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'type' => 1,
            'text' => 'Your choice',
            'order' => 1,
        ]);

        Option::create(['question_id' => $question->id, 'text' => 'Option A', 'order' => 1]);

        $response = $this->getJson("/api/surveys/{$survey->id}/results");

        $response->assertStatus(200);
    }

    /**
     * Test admin can export results for any survey.
     */
    public function test_admin_can_export_results_for_any_survey(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => "Author's Survey",
            'status' => 2,
        ]);

        $response = $this->getJson("/api/surveys/{$survey->id}/results/export");

        $response->assertStatus(200);
    }
}
