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

    public function verifyTransaction($transactionRef)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])->get("https://sandbox.monnify.com/api/v2/transactions/{$transactionRef}");

            if ($response->successful()) {
                return $response['responseBody'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Log::channel('daily')->info('Error: '.$e->getMessage());
        }
    }
}
