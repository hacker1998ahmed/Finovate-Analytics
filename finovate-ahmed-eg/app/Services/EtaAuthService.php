<?php

namespace App\Services;

use App\Models\Company;
use App\Models\EtaToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EtaAuthService
{
    private Company $company;
    private string $baseUrl;

    public function __construct(Company $company)
    {
        $this->company = $company;
        $this->baseUrl = $company->api_base_url ?? 'https://api.invoicing.eta.gov.eg';
    }

    /**
     * Get or refresh access token for the company.
     */
    public function getAccessToken(): ?string
    {
        // Check for existing valid token
        $token = EtaToken::valid()
            ->where('company_id', $this->company->id)
            ->latest('expires_at')
            ->first();

        if ($token && !$token->willExpireSoon(10)) {
            return $token->access_token;
        }

        // Token expired or will expire soon, get new one
        return $this->requestNewToken();
    }

    /**
     * Request a new access token from ETA.
     */
    private function requestNewToken(): ?string
    {
        try {
            $response = Http::asForm()->post("{$this->baseUrl}/connect/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->company->eta_client_id,
                'client_secret' => $this->company->getDecryptedClientSecretAttribute(),
                'scope' => 'InvoicingAPI',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Save the new token
                $this->saveToken($data);
                
                return $data['access_token'] ?? null;
            }

            Log::error('ETA Token Request Failed', [
                'company_id' => $this->company->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('ETA Token Request Exception', [
                'company_id' => $this->company->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Save the token to database.
     */
    private function saveToken(array $data): void
    {
        EtaToken::create([
            'company_id' => $this->company->id,
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_in' => $data['expires_in'] ?? 3600,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);
    }

    /**
     * Make an authenticated HTTP request to ETA API.
     */
    public function request(string $method, string $endpoint, array $data = [], array $options = [])
    {
        $token = $this->getAccessToken();

        if (!$token) {
            throw new Exception('Failed to obtain access token from ETA');
        }

        $url = str_starts_with($endpoint, 'http') ? $endpoint : "{$this->baseUrl}/{$endpoint}";

        $request = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        // Add any additional headers from options
        if (isset($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $request = $request->withHeader($key, $value);
            }
        }

        switch (strtolower($method)) {
            case 'get':
                return $request->get($url, $data);
            case 'post':
                return $request->post($url, $data);
            case 'put':
                return $request->put($url, $data);
            case 'delete':
                return $request->delete($url, $data);
            default:
                throw new Exception("Unsupported HTTP method: {$method}");
        }
    }

    /**
     * Get token information.
     */
    public function getTokenInfo(): ?array
    {
        $token = EtaToken::valid()
            ->where('company_id', $this->company->id)
            ->latest('expires_at')
            ->first();

        if (!$token) {
            return null;
        }

        return [
            'has_token' => true,
            'expires_at' => $token->expires_at->toDateTimeString(),
            'is_expired' => $token->isExpired(),
            'will_expire_soon' => $token->willExpireSoon(),
        ];
    }

    /**
     * Revoke all tokens for the company.
     */
    public function revokeTokens(): bool
    {
        try {
            EtaToken::where('company_id', $this->company->id)->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Failed to revoke ETA tokens', [
                'company_id' => $this->company->id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
