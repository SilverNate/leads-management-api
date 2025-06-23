<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Lead;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LeadApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected string $apiToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiToken = env('API_BEARER_TOKEN', 'test_api_token');

        // Mock the third-party HTTP request to avoid actual external calls during tests
        Http::fake([
            env('THIRD_PARTY_API_URL') . '*' => Http::response('OK', 200),
        ]);
    }

    /**
     * @test
     * Test POST /api/leads endpoint - successful creation.
     */
    public function it_can_create_a_lead()
    {

        $leadData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'source' => 'test_suite',
            'message' => $this->faker->sentence,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/leads', $leadData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Lead created and forwarded successfully.',
            ]);

        $this->assertDatabaseHas('leads', [
            'email' => $leadData['email'],
            'name' => $leadData['name'],
        ]);

        $this->assertFalse(Cache::has('all_leads'));

        Http::assertSent(function ($request) use ($leadData) {
            return $request->url() == env('THIRD_PARTY_API_URL') &&
                $request->method() == 'POST' &&
                str_contains($request->body(), $leadData['email']);
        });
    }

    /**
     * @test
     * Test POST /api/leads endpoint - validation errors.
     */
    public function it_returns_validation_errors_for_invalid_lead_data()
    {
        $leadData = [
            'email' => 'invalid-email',
            'phone' => '12345',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/leads', $leadData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);

        $this->assertDatabaseMissing('leads', ['email' => 'invalid-email']);
    }

    /**
     * @test
     * Test POST /api/leads endpoint - unique email constraint.
     */
    public function it_prevents_duplicate_lead_emails()
    {
        Lead::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '123-123-1234',
        ]);

        $leadData = [
            'name' => 'New User',
            'email' => 'existing@example.com', // Duplicate email
            'phone' => '456-456-4567',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/leads', $leadData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


    /**
     * @test
     * Test GET /api/leads endpoint - retrieve all leads.
     */
    public function it_can_retrieve_all_leads()
    {
        Lead::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Leads retrieved successfully.',
            ])
            ->assertJsonCount(3, 'leads');

        $this->assertTrue(Cache::has('all_leads'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'leads');
    }

    /**
     * @test
     * Test GET /api/leads/{id} endpoint - successful retrieval.
     */
    public function it_can_retrieve_a_specific_lead_by_id()
    {
        $lead = Lead::factory()->create(); // Create a single lead

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->getJson('/api/leads/' . $lead->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Lead retrieved successfully.',
                'lead' => [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'email' => $lead->email,
                ],
            ]);
    }

    /**
     * @test
     * Test GET /api/leads/{id} endpoint - lead not found.
     */
    public function it_returns_404_if_lead_is_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->getJson('/api/leads/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Lead not found.',
            ]);
    }

    /**
     * @test
     * Test API authentication - no token provided.
     */
    public function it_returns_unauthorized_if_no_api_token_is_provided()
    {
        $response = $this->postJson('/api/leads', []); // No headers

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API token.',
            ]);
    }

    /**
     * @test
     * Test API authentication - invalid token provided.
     */
    public function it_returns_unauthorized_if_invalid_api_token_is_provided()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->postJson('/api/leads', []);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API token.',
            ]);
    }

    /**
     * @test
     * Test API authentication with correct token.
     */
    public function it_allows_access_with_valid_api_token()
    {
        $leadData = [
            'name' => 'Valid Test',
            'email' => $this->faker->unique()->safeEmail,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/leads', $leadData);

        $response->assertStatus(201);
    }
}
