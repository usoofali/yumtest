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
    protected $v1;
    protected $v2;


    public function __construct()
    {
        $this->initializeAuth();
        $mode = "demo";
        if($mode == "live"){
            $this->v1 = 'https://api.monnify.com/api/v1';
            $this->v2 = 'https://api.monnify.com/api/v2';
        }
        else{
            $this->v1 = 'https://api.monnify.com/api/v1';
            $this->v2 = 'https://api.monnify.com/api/v2';
        }
        
       
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
            ])->post($this->v1  . '/auth/login/');

            if ($response->successful()) {
                $this->accessToken = $response['responseBody']['accessToken'];
            } else {
                Log::channel('daily')->error('Failed to retrieve access token.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error' => $response->json('responseMessage', 'Unknown error'),
                ]);
            }
            
        } catch (\Exception $e) {
            Log::channel('daily')->info('Error: ' . $e->getMessage());
        }
    }

    public function initializeTransaction($amount, $customerName, $customerEmail, $paymentReference, $paymentDescription, $currencyCode, $contractCode, $redirectUrl)
    {
        try {
            $api_key = app(RazorpaySettings::class)->key_id;
            $secret_key = app(RazorpaySettings::class)->key_secret;
            $webhook_secret = app(RazorpaySettings::class)->webhook_secret;

            Log::channel('daily')->info('Monnify Credentials:', [
                'api_key' => $api_key,
                'secret_key' => $secret_key,
                'webhook_secret' => $webhook_secret,
                'accessToken' => $this->accessToken
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json'
            ])->withBasicAuth($api_key, $secret_key)->asJson()->post($this->v1 . '/merchant/transactions/init-transaction', [
                        'amount' => $amount,
                        'customerName' => $customerName,
                        'customerEmail' => $customerEmail,
                        'paymentReference' => $paymentReference,
                        'paymentDescription' => $paymentDescription,
                        'currencyCode' => $currencyCode,
                        'contractCode' => $webhook_secret,
                        'redirectUrl' => $redirectUrl
                    ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                
                return [
                    'paymentReference' => $paymentReference,
                    'status' => false,
                    'response' => "Transaction initialization failed.",
                    'payload' => [
                        'amount' => $amount,
                        'customerName' => $customerName,
                        'customerEmail' => $customerEmail,
                        'paymentReference' => $paymentReference,
                        'paymentDescription' => $paymentDescription,
                        'currencyCode' => $currencyCode,
                        'contractCode' => $webhook_secret,
                        'redirectUrl' => $redirectUrl
                    ]
                ];
            }
        } catch (\Exception $e) {
            return [
                'paymentReference' => $paymentReference,
                'status' => false,
                'response' => "Error during transaction initialization.",
                'payload' => [
                    'amount' => $amount,
                    'customerName' => $customerName,
                    'customerEmail' => $customerEmail,
                    'paymentReference' => $paymentReference,
                    'paymentDescription' => $paymentDescription,
                    'currencyCode' => $currencyCode,
                    'contractCode' => $webhook_secret,
                    'redirectUrl' => $redirectUrl
                ]
            ];
        }
    }

    public function verifyTransaction($transactionRef)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])->get($this->v2 . "/transactions/{$transactionRef}");

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
