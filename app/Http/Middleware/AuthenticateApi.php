<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;


class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the API token from environment variables
        $apiToken = env('API_BEARER_TOKEN');

        // Check if the API token is set in the environment
        if (empty($apiToken)) {
            // Log an error if the API token is not configured
            // For production, you might want to throw an exception or return a generic error.
            // For now, we'll return a 500 error indicating misconfiguration.
            return response()->json([
                'status' => 'error',
                'message' => 'API token not configured on the server.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Get the Bearer token from the request header
        $bearerToken = $request->bearerToken();

        // Check if the Bearer token is present and matches the configured API token
        if (!$bearerToken || $bearerToken !== $apiToken) {
            // Log the unauthorized access attempt
            $this->logError('Unauthorized access attempt', $request->path(), Response::HTTP_UNAUTHORIZED);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
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
            \App\Models\ErrorLog::create([
                'error_message' => $message,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        } catch (\Exception $e) {
            // Log to default Laravel logs if logging to DB fails
            Log::error('Failed to log error to database: ' . $e->getMessage(), [
                'original_message' => $message,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
            ]);
        }
    }
}
