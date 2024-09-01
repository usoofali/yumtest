<?php
// app/Services/MonnifyService.php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use App\Settings\RazorpaySettings;
use Illuminate\Support\Facades\Log;

class MonnifyService
{
    protected $authHeader;
    protected $accessToken;

    public function __construct()
    {
        $this->initializeAuth();
    }

    private function initializeAuth()
    {
        try {
            $api_key = app(RazorpaySettings::class)->key_id;
            $secret_key = app(RazorpaySettings::class)->key_secret;
            $authString = $api_key . ':' . $secret_key;
            $this->authHeader = base64_encode($authString);

            $response = Http::withHeaders([
                'Authorization' => "Basic {$this->authHeader}",
            ])->post(config('monnify.base_url') . '/auth/login/');

            if ($response->successful()) {
                $this->accessToken = $response['responseBody']['accessToken'];
                Log::info('Successfully retrieved access token.', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            } else {
                Log::error('Failed to retrieve access token.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error' => $response->json('responseMessage', 'Unknown error'),
                ]);
            }
            
        } catch (\Exception $e) {
            Log::channel('daily')->info('Error: ' . $e->getMessage());
        }
    }

    public function initializeTransaction($amount, $customerName, $customerEmail, $paymentReference, $paymentDescription, $currencyCode, $contractCode, $redirectUrl, $paymentMethods, $incomeSplitConfig = null, $metadata = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json'
            ])->post(config('monnify.base_url') . '/merchant/transactions/init-transaction', [
                        'amount' => $amount,
                        'customerName' => $customerName,
                        'customerEmail' => $customerEmail,
                        'paymentReference' => $paymentReference,
                        'paymentDescription' => $paymentDescription,
                        'currencyCode' => $currencyCode,
                        'contractCode' => $contractCode,
                        'redirectUrl' => $redirectUrl,
                        'paymentMethods' => $paymentMethods,
                        'incomeSplitConfig' => $incomeSplitConfig,
                        'metadata' => $metadata
                    ]);

            if ($response->successful()) {
                Log::info("Transaction initialized successfully.", [
                    'paymentReference' => $paymentReference,
                    'response' => $response->json()
                ]);
                return $response->json();
            } else {
                Log::warning("Transaction initialization failed.", [
                    'paymentReference' => $paymentReference,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error during transaction initialization.', [
                'exception' => $e->getMessage(),
                'paymentReference' => $paymentReference
            ]);
            return false;
        }
    }

    public function verifyTransaction($transactionRef)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])->get("https://api.monnify.com/api/v2/transactions/{$transactionRef}");

            if ($response->successful()) {
                Log::channel('daily')->info("Transaction verified successfully.", [
                    'transactionRef' => $transactionRef,
                    'response' => $response->json() // Log the decoded JSON response
                ]);
                return $response->json(); // Decode the JSON response and return it
            } else {
                Log::channel('daily')->warning("Transaction verification failed.", [
                    'transactionRef' => $transactionRef,
                    'status' => $response->status(),
                    'response' => $response->body() // Log the raw response body
                ]);
                return false; // Indicate failure
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error('Error during transaction verification.', [
                'exception' => $e->getMessage(),
                'transactionRef' => $transactionRef
            ]);
            return false; // Indicate failure
        }
    }

}
