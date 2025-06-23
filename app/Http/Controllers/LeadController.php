<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LeadController extends Controller
{

    public function store(Request $request)
    {
        try {
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

            $lead = Lead::create($request->all());

            Cache::forget('all_leads');

            $this->forwardLeadToThirdParty($lead);

            return response()->json([
                'status' => 'success',
                'message' => 'Lead created and forwarded successfully.',
                'lead' => $lead,
            ], 201);
        } catch (\Exception $e) {
            $this->logError($e->getMessage(), '/api/leads', 500);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request.',
                'details' => config('app.debug') ? $e->getMessage() : null, // Only show details in debug mode
            ], 500);
        }
    }


    public function index()
    {
        try {
            //chace 60 minutes
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

    private function logError(string $message, string $endpoint, int $statusCode): void
    {
        try {
            ErrorLog::create([
                'error_message' => $message,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log error to database: ' . $e->getMessage(), [
                'original_message' => $message,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        }
    }


    private function forwardLeadToThirdParty(Lead $lead): void
    {
        $thirdPartyApiUrl = env('THIRD_PARTY_API_URL');
        $thirdPartyApiKey = env('THIRD_PARTY_API_KEY');

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
