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
            $authString = $api_key . ':' . $secret_key ;
            $this->authHeader = base64_encode($authString);

            $response = Http::withHeaders([
                'Authorization' => "Basic {$this->authHeader}",
            ])->post(config('monnify.base_url') . '/auth/login/');

            if ($response->successful()) {
                $this->accessToken = $response['responseBody']['accessToken'];

            } else {
                Log::channel('daily')->info('Error: Failed to get access token.');
            }
        } catch (\Exception $e) {
            Log::channel('daily')->info('Error: '.$e->getMessage());
        }
    }

    public function initTransaction(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ])->post(config('monnify.base_url'.'api/v1') . '/merchant/transactions/init-transaction', $data);

            if ($response->successful()) {
                return $response['responseBody'];
            } else {
                Log::channel('daily')->info('Failed to initialize transaction: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::channel('daily')->info('Error: '.$e->getMessage());
        }
    }

    public function verifyTransaction($transactionRef)
    {
        try {
            Log::channel('daily')->info('Access Token: '.$this->accessToken);
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])->get(config('monnify.base_url'.'api/v2') . "/transactions/{$transactionRef}");

            if ($response->successful()) {
                Log::channel('daily')->info('Success: good response.');
                return $response['responseBody'];
            } else {
                Log::channel('daily')->info('Error: No good response.');
                return $response->body();
            }
        } catch (\Exception $e) {
            Log::channel('daily')->info('Error: '.$e->getMessage());
        }
    }
}
