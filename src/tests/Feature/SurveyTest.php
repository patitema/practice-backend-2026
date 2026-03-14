<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Status;
use App\Models\Survey;
use Laravel\Sanctum\Sanctum;

class SurveyTest extends TestCase
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
     * Test get published surveys.
     */
    public function test_can_get_published_surveys(): void
    {
        $author = $this->createAuthor();

        Survey::create([
            'author_id' => $author->id,
            'title' => 'Published Survey',
            'description' => 'Test',
            'status' => 2,
        ]);

        Survey::create([
            'author_id' => $author->id,
            'title' => 'Draft Survey',
            'description' => 'Test',
            'status' => 1,
        ]);

        $response = $this->getJson('/api/surveys');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Published Survey']);
    }

    /**
     * Test create survey.
     */
    public function test_authenticated_user_can_create_survey(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $response = $this->postJson('/api/surveys', [
            'title' => 'New Survey',
            'description' => 'Test description',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'New Survey',
                    'description' => 'Test description',
                ],
            ]);

        $this->assertDatabaseHas('surveys', ['title' => 'New Survey']);
    }

    /**
     * Test create survey requires authentication.
     */
    public function test_create_survey_requires_authentication(): void
    {
        $response = $this->postJson('/api/surveys', [
            'title' => 'New Survey',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update survey.
     */
    public function test_author_can_update_draft_survey(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Original Title',
            'description' => 'Original description',
            'status' => 1,
        ]);

        $response = $this->putJson("/api/surveys/{$survey->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('surveys', ['title' => 'Updated Title']);
    }

    /**
     * Test cannot update published survey.
     */
    public function test_cannot_update_published_survey(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Published Survey',
            'status' => 2,
        ]);

        $response = $this->putJson("/api/surveys/{$survey->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Only draft surveys can be edited']);
    }

    /**
     * Test delete survey.
     */
    public function test_author_can_delete_survey(): void
    {
        $author = $this->createAuthor();
        Sanctum::actingAs($author);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => 'Survey to delete',
            'status' => 1,
        ]);

        $response = $this->deleteJson("/api/surveys/{$survey->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
    }

    /**
     * Test admin can delete any survey.
     */
    public function test_admin_can_delete_any_survey(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => "Author's Survey",
            'status' => 1,
        ]);

        $response = $this->deleteJson("/api/surveys/{$survey->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
    }

    /**
     * Test admin can close any survey.
     */
    public function test_admin_can_close_any_survey(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin);

        $survey = Survey::create([
            'author_id' => $author->id,
            'title' => "Author's Survey",
            'status' => 2, // published
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/close");

        $response->assertStatus(200);
        $this->assertDatabaseHas('surveys', ['id' => $survey->id, 'status' => 3]); // closed
    }
}
