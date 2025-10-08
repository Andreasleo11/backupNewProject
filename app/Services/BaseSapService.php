<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaseSapService
{
    private string $baseUrl;

    private string $authPath;

    private string $companyDb;

    private string $username;

    private string $password;

    // Cache key + TTL
    private const TOKEN_CACHE_KEY = 'sap_token';

    private const TOKEN_TTL_SECONDS = 55 * 60; // adjust to your API's expires_in

    public function __construct()
    {
        // Move all config to env
        $this->baseUrl = rtrim(config('sap.base_url', 'http://192.168.6.149:9001'), '/');
        $this->authPath = config('sap.auth_path', '/auth/token');
        $this->companyDb = config('sap.company_db', 'LIVE_DATABASE');
        $this->username = config('sap.username', 'it02');
        $this->password = config('sap.password', '123it');
    }

    /** Public accessor if you really need the token string */
    public function getToken(): string
    {
        return $this->ensureToken();
    }

    /** Example GET */
    public function get(string $endpoint, array $params = []): array
    {
        $token = $this->ensureToken();

        $response = $this->http()
            ->withToken($token)
            ->acceptJson()
            ->get($this->baseUrl.$endpoint, $params);

        if ($response->status() === 401) {
            // refresh once on 401
            $token = $this->refreshToken();
            $response = $this->http()
                ->withToken($token)
                ->acceptJson()
                ->get($this->baseUrl.$endpoint, $params);
        }

        $response->throw(); // raise if 4xx/5xx

        return $response->json() ?? [];
    }

    /** Example POST */
    public function post(string $endpoint, array $payload = []): array
    {
        $token = $this->ensureToken();

        $response = $this->http()
            ->withToken($token)
            ->acceptJson()
            ->post($this->baseUrl.$endpoint, $payload);

        if ($response->status() === 401) {
            $token = $this->refreshToken();
            $response = $this->http()
                ->withToken($token)
                ->acceptJson()
                ->post($this->baseUrl.$endpoint, $payload);
        }

        $response->throw();

        return $response->json() ?? [];
    }

    /** --- Internals --- */
    private function ensureToken(): string
    {
        $token = Cache::get(self::TOKEN_CACHE_KEY);
        if ($token) {
            return $token;
        }

        return $this->refreshToken();
    }

    private function refreshToken(): string
    {
        // DO NOT set a fake Host header. Let cURL set Host correctly for 192.168.6.149.
        $response = $this->http()
            ->asJson()          // explicit JSON
            ->acceptJson()
            ->post($this->baseUrl.$this->authPath, [
                'CompanyDB' => $this->companyDb,
                'Username' => $this->username,
                'Password' => $this->password,
            ]);

        // Helpful logging for diagnosing server-side empty replies
        if (! $response->successful()) {
            Log::warning('SAP auth failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);
        }

        $response->throw(); // will include body if any

        $data = $response->json() ?? [];

        // Adjust keys according to your API response shape
        $token = $data['access_token'] ?? $data['token'] ?? null;

        if (! $token) {
            throw new \RuntimeException('SAP auth missing token field.');
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, self::TOKEN_TTL_SECONDS);

        return $token;
    }

    private function http()
    {
        return Http::retry(2, 200)
            ->timeout(10)
            // Some servers misbehave on HTTP/2 prior knowledge; force HTTP/1.1 helps avoid cURL 52/Empty reply
            ->withOptions(['version' => CURL_HTTP_VERSION_1_1]);
    }
}
