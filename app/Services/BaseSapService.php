<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class BaseSapService
{
    protected $baseUrl = 'http://192.168.6.149:9001';
    protected $authUrl = 'http://192.168.6.149:9001/auth/token';
    protected $token;

    public function __construct()
    {
        $this->token = Session::get('sap_token');

        if (!$this->token) {
            $this->token = $this->authenticate();
            Session::put('sap_token', $this->token);
        }
    }

    /**
     * Authenticate to SAP API and get the token
     */
    protected function authenticate()
    {
        $response = Http::withHeaders([
                'Host' => 'localhost',
                'Content-Type' => 'application/json',
            ])
            ->post($this->authUrl, [
                'CompanyDB' => 'LIVE_DATABASE',
                'Username' => 'it02',
                'Password' => '123it',
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('Failed to authenticate to SAP: ' . $response->body());
    }

    /**
     * Send a GET request to SAP API
     */
    protected function get($endpoint, $params = [])
    {
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Host' => 'localhost', // ✅ INI YANG WAJIB
            ])
            ->get($this->baseUrl . $endpoint, $params);

        if ($response->status() === 401) {
            // Refresh token jika expired
            $this->token = $this->authenticate();
            session(['sap_token' => $this->token]);

            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/json',
                    'Host' => 'localhost', // tetap perlu
                ])
                ->get($this->baseUrl . $endpoint, $params);
        }

        return $response->json();
    }

    public function getToken()
    {
        return $this->token;
    }

    public function testGet($endpoint, $params = [])
    {
        return $this->get($endpoint, $params);
    }
}
