<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveController extends Controller
{
    /**
     * Get list of Nigerian banks from Flutterwave
     */
    public function getBanks(Request $request)
    {
        try {
            $country = $request->query('country', 'NG');
            
            // Cache banks for 24 hours
            $cacheKey = "flutterwave_banks_{$country}";
            
            $banks = Cache::remember($cacheKey, 86400, function () use ($country) {
                $base = rtrim(config('services.flutterwave.base_url'), '/');
                $url = "{$base}/banks/{$country}";
                
                $response = Http::withToken(config('services.flutterwave.secret'))
                    ->timeout(15)
                    ->get($url);
                
                if (!$response->successful()) {
                    Log::error('Flutterwave banks API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return [];
                }
                
                $data = $response->json();
                
                if (($data['status'] ?? '') !== 'success') {
                    Log::error('Flutterwave banks API failed', ['response' => $data]);
                    return [];
                }
                
                return $data['data'] ?? [];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $banks,
            ]);
        } catch (\Throwable $e) {
            Log::error('Flutterwave getBanks exception', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch banks',
            ], 500);
        }
    }
    
    /**
     * Verify account number with Flutterwave
     */
    public function verifyAccount(Request $request)
    {
        // Validate and normalize bank_code
        $request->validate([
            'account_number' => 'required|string|max:20',
            'bank_code' => 'required',
        ]);
        
        // Ensure bank_code is numeric (accepts both string and numeric)
        $bankCode = $request->bank_code;
        if (!is_numeric($bankCode)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bank code must be numeric',
            ], 400);
        }
        
        $bankCode = (string) $bankCode;
        
        try {
            $base = rtrim(config('services.flutterwave.base_url'), '/');
            // Use the correct Flutterwave v3 endpoint
            $url = "{$base}/accounts/resolve";
  
            $payload = [
                'account_number' => $request->account_number,
                'account_bank' => $bankCode,
            ];
            
            Log::info('Flutterwave account verification request', [
                'url' => $url,
                'payload' => $payload,
            ]);
            
            $response = Http::withToken(config('services.flutterwave.secret'))
                ->timeout(15)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
            
            $statusCode = $response->status();
            $responseBody = $response->body();
            $data = $response->json();
            
            if (!$response->successful()) {
                Log::error('Flutterwave account verification API error', [
                    'status' => $statusCode,
                    'body' => $responseBody,
                    'json' => $data,
                    'account_number' => $request->account_number,
                    'bank_code' => $bankCode,
                    'url' => $url,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $data['message'] ?? ($data['data']['message'] ?? 'Failed to verify account'),
                ], 400);
            }
            
            // Check if response is null or empty
            if ($data === null || empty($data)) {
                Log::error('Flutterwave account verification returned empty response', [
                    'status' => $statusCode,
                    'body' => $responseBody,
                    'account_number' => $request->account_number,
                    'bank_code' => $bankCode,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid response from bank verification service',
                ], 400);
            }
            
            // Check response status
            if (($data['status'] ?? '') !== 'success') {
                Log::warning('Flutterwave account verification failed', [
                    'response' => $data,
                    'status_code' => $statusCode,
                    'account_number' => $request->account_number,
                    'bank_code' => $bankCode,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $data['message'] ?? ($data['data']['message'] ?? 'Account verification failed'),
                ], 400);
            }
            
            // Extract account name from response
            $accountName = $data['data']['account_name'] ?? $data['data']['accountName'] ?? '';
            $accountNumber = $data['data']['account_number'] ?? $data['data']['accountNumber'] ?? $request->account_number;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Flutterwave verifyAccount exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify account',
            ], 500);
        }
    }
}

