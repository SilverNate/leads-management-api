<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http; // For third-party service integration
use Illuminate\Support\Facades\Log; // For general logging
use Illuminate\Support\Facades\Cache; // For caching

class LeadController extends Controller
{
    /**
     * Store a newly created lead in storage and forward to a third-party service.
     *
     * @OA\Post(
     * path="/api/leads",
     * summary="Store a new lead",
     * tags={"Leads"},
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="application/json",
     * @OA\Schema(
     * type="object",
     * required={"name", "email"},
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * @OA\Property(property="phone", type="string", nullable=true, example="123-456-7890"),
     * @OA\Property(property="source", type="string", nullable=true, example="website"),
     * @OA\Property(property="message", type="string", nullable=true, example="Interested in product X."),
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Lead created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Lead created and forwarded successfully."),
     * @OA\Property(property="lead", type="object", ref="#/components/schemas/Lead")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="error"),
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object", example={"email": {"The email has already been taken."}})
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="error"),
     * @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     * )
     * )
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:leads,email',
                'phone' => 'nullable|string|max:20',
                'source' => 'nullable|string|max:255',
                'message' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $this->logError('Validation error for POST /api/leads', '/api/leads', 422);
                return response()->json([
                    'status' => 'error',
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the lead in the database
            $lead = Lead::create($request->all());

            // Clear the cache for all leads as data has changed
            Cache::forget('all_leads');

            // Forward lead data to a third-party service (e.g., Slack Webhook or Mailchimp)
            $this->forwardLeadToThirdParty($lead);

            return response()->json([
                'status' => 'success',
                'message' => 'Lead created and forwarded successfully.',
                'lead' => $lead,
            ], 201);
        } catch (\Exception $e) {
            // Log the error
            $this->logError($e->getMessage(), '/api/leads', 500);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request.',
                'details' => config('app.debug') ? $e->getMessage() : null, // Only show details in debug mode
            ], 500);
        }
    }

    /**
     * Retrieve all leads from PostgreSQL.
     *
     * @OA\Get(
     * path="/api/leads",
     * summary="Retrieve all leads",
     * tags={"Leads"},
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="List of leads",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Leads retrieved successfully."),
     * @OA\Property(
     * property="leads",
     * type="array",
     * @OA\Items(ref="#/components/schemas/Lead")
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="error"),
     * @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     * )
     * )
     * )
     */
    public function index()
    {
        try {
            // Retrieve all leads from cache or database
            // Cache for 60 minutes (adjust as needed)
            $leads = Cache::remember('all_leads', 60 * 60, function () {
                return Lead::all();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Leads retrieved successfully.',
                'leads' => $leads,
            ], 200);
        } catch (\Exception $e) {
            $this->logError($e->getMessage(), '/api/leads', 500);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving leads.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Retrieve details of a specific lead.
     *
     * @OA\Get(
     * path="/api/leads/{id}",
     * summary="Retrieve details of a specific lead",
     * tags={"Leads"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the lead to retrieve",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Lead details",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(property="message", type="string", example="Lead retrieved successfully."),
     * @OA\Property(property="lead", type="object", ref="#/components/schemas/Lead")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Lead not found",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="error"),
     * @OA\Property(property="message", type="string", example="Lead not found.")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="error"),
     * @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     * )
     * )
     * )
     */
    public function show(string $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                $this->logError("Lead with ID {$id} not found", "/api/leads/{$id}", 404);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lead not found.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lead retrieved successfully.',
                'lead' => $lead,
            ], 200);
        } catch (\Exception $e) {
            $this->logError($e->getMessage(), "/api/leads/{$id}", 500);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the lead.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Log an error to the error_logs database.
     *
     * @param string $message The error message.
     * @param string $endpoint The API endpoint that was accessed.
     * @param int $statusCode The HTTP status code of the response.
     */
    private function logError(string $message, string $endpoint, int $statusCode): void
    {
        try {
            // Ensure the ErrorLog model uses the correct connection defined in config/database.php
            ErrorLog::create([
                'error_message' => $message,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        } catch (\Exception $e) {
            // Fallback: Log to Laravel's default log file if database logging fails
            Log::error('Failed to log error to database: ' . $e->getMessage(), [
                'original_message' => $message,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        }
    }

    /**
     * Forward lead data to a third-party service.
     * This is a placeholder example for a Slack Webhook.
     * You would replace this with actual Mailchimp API integration, etc.
     *
     * @param Lead $lead The lead model instance.
     */
    private function forwardLeadToThirdParty(Lead $lead): void
    {
        $thirdPartyApiUrl = env('THIRD_PARTY_API_URL');
        $thirdPartyApiKey = env('THIRD_PARTY_API_KEY'); // Or specific webhook secret

        if (empty($thirdPartyApiUrl)) {
            Log::warning('THIRD_PARTY_API_URL is not set. Skipping lead forwarding.');
            $this->logError('THIRD_PARTY_API_URL not set for lead forwarding.', 'Internal', 400);
            return;
        }

        try {
            // Example for a Slack Webhook
            $response = Http::post($thirdPartyApiUrl, [
                'text' => "New Lead Submitted!\n" .
                    "Name: {$lead->name}\n" .
                    "Email: {$lead->email}\n" .
                    "Phone: {$lead->phone}\n" .
                    "Source: {$lead->source}\n" .
                    "Message: {$lead->message}\n" .
                    "Created At: {$lead->created_at}",
                // Add any other required fields for your specific third-party API
            ]);

            if ($response->successful()) {
                Log::info('Lead successfully forwarded to third-party service.', ['lead_id' => $lead->id]);
            } else {
                Log::error('Failed to forward lead to third-party service.', [
                    'lead_id' => $lead->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                $this->logError('Failed to forward lead to third-party service: ' . $response->body(), '/api/leads', $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Exception while forwarding lead to third-party service: ' . $e->getMessage(), ['lead_id' => $lead->id]);
            $this->logError('Exception during third-party forwarding: ' . $e->getMessage(), '/api/leads', 500);
        }
    }
}
