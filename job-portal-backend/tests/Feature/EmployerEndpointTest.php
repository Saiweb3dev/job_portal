<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployerEndpointTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $employer;
    private $applicant;
    private $token;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a category for job associations
        $this->category = Category::create([
            'name' => 'Technology',
            'description' => 'Technology related jobs'
        ]);

        // Create an employer user
        $this->employer = User::create([
            'name' => 'Test Employer',
            'email' => 'employer@example.com',
            'password' => bcrypt('password'),
            'role' => 'employer'
        ]);

        // Create an applicant user for testing unauthorized access
        $this->applicant = User::create([
            'name' => 'Test Applicant',
            'email' => 'applicant@example.com',
            'password' => bcrypt('password'),
            'role' => 'applicant'
        ]);

        // Login as employer and get token
        $response = $this->postJson('/api/login', [
            'email' => 'employer@example.com',
            'password' => 'password'
        ]);

        $this->token = $response->json('token');
    }

    public function test_employer_can_create_job(): void
    {
        $jobData = [
            'title' => 'Software Developer',
            'description' => 'We are looking for a skilled software developer',
            'location' => 'Remote',
            'salary' => 80000,
            'type' => 'full-time',
            'category_id' => $this->category->id,
            'deadline' => now()->addWeeks(2)->toDateString()
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/jobs', $jobData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'job' => [
                        'id', 'title', 'description', 'location', 'salary',
                        'type', 'deadline', 'status', 'user_id', 'category_id'
                    ]
                ]);

        $this->assertDatabaseHas('jobs', [
            'title' => 'Software Developer',
            'user_id' => $this->employer->id,
        ]);
    }

    public function test_employer_can_update_job(): void
    {
        // Create a job for the employer
        $job = Job::create([
            'title' => 'Original Job Title',
            'description' => 'Original description',
            'location' => 'Original location',
            'salary' => 50000,
            'type' => 'full-time',
            'user_id' => $this->employer->id,
            'category_id' => $this->category->id,
            'deadline' => now()->addMonths(1)->toDateString(),
            'status' => 'open',
            'applications_count' => 0
        ]);

        $updateData = [
            'title' => 'Updated Job Title',
            'salary' => 60000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/jobs/{$job->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('job.title', 'Updated Job Title')
                ->assertJsonPath('job.salary', 60000);

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'title' => 'Updated Job Title',
            'salary' => 60000
        ]);
    }

    public function test_employer_can_delete_job(): void
    {
        // Create a job for the employer with no applications
        $job = Job::create([
            'title' => 'Job to Delete',
            'description' => 'This job will be deleted',
            'location' => 'Test location',
            'salary' => 40000,
            'type' => 'part-time',
            'user_id' => $this->employer->id,
            'category_id' => $this->category->id,
            'deadline' => now()->addMonths(1)->toDateString(),
            'status' => 'open',
            'applications_count' => 0
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/jobs/{$job->id}");

        $response->assertStatus(200)
                ->assertJsonPath('message', 'Job deleted successfully');

        $this->assertDatabaseMissing('jobs', [
            'id' => $job->id
        ]);
    }

    public function test_employer_cannot_delete_job_with_applications(): void
    {
        // Create a job for the employer with applications
        $job = Job::create([
            'title' => 'Job with Applications',
            'description' => 'This job has applications',
            'location' => 'Test location',
            'salary' => 45000,
            'type' => 'remote',
            'user_id' => $this->employer->id,
            'category_id' => $this->category->id,
            'deadline' => now()->addMonths(1)->toDateString(),
            'status' => 'open',
            'applications_count' => 5  // Has applications
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/jobs/{$job->id}");

        $response->assertStatus(400)
                ->assertJsonPath('message', 'Cannot delete job with existing applications');

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id
        ]);
    }

    public function test_employer_cannot_modify_another_employers_job(): void
    {
        // Create another employer
        $anotherEmployer = User::create([
            'name' => 'Another Employer',
            'email' => 'another@example.com',
            'password' => bcrypt('password'),
            'role' => 'employer'
        ]);

        // Create a job owned by another employer
        $job = Job::create([
            'title' => 'Another Employer Job',
            'description' => 'This job belongs to another employer',
            'location' => 'Another location',
            'salary' => 55000,
            'type' => 'contract',
            'user_id' => $anotherEmployer->id,
            'category_id' => $this->category->id,
            'deadline' => now()->addMonths(1)->toDateString(),
            'status' => 'open',
            'applications_count' => 0
        ]);

        // Try to update
        $updateResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/jobs/{$job->id}", ['title' => 'Hacked Job']);

        $updateResponse->assertStatus(403);

        // Try to delete
        $deleteResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/jobs/{$job->id}");

        $deleteResponse->assertStatus(403);
    }

    public function test_applicant_cannot_access_employer_endpoints(): void
    {
        // Login as applicant
        $applicantResponse = $this->postJson('/api/login', [
            'email' => 'applicant@example.com',
            'password' => 'password'
        ]);

        $applicantToken = $applicantResponse->json('token');

        // Try to create a job
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $applicantToken,
        ])->postJson('/api/jobs', [
            'title' => 'Unauthorized Job',
            'description' => 'This should fail',
            'location' => 'Test',
            'type' => 'full-time',
            'category_id' => $this->category->id,
            'deadline' => now()->addWeeks(2)->toDateString()
        ]);

        $response->assertStatus(403);
    }
}
