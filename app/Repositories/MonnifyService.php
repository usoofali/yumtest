<?php
// app/Services/MonnifyService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Settings\RazorpaySettings;
use Exception;

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
                throw new Exception('Failed to retrieve access token: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception("Error initializing authentication: " . $e->getMessage());
        }
    }

    public function initTransaction(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ])->post(config('monnify.base_url') . '/merchant/transactions/init-transaction', $data);

            if ($response->successful()) {
                return $response['responseBody'];
            } else {
                throw new Exception('Failed to initialize transaction: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception("Error initializing transaction: " . $e->getMessage());
        }
    }

    public function verifyTransaction($transactionRef)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
            ])->get(config('monnify.base_url') . "/transactions/{$transactionRef}");

            if ($response->successful()) {
                return $response['responseBody'];
            } else {
                throw new Exception('Failed to verify transaction: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception("Error verifying transaction: " . $e->getMessage());
        }
    }
}
