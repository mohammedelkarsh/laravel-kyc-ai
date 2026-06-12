<?php

declare(strict_types=1);

namespace KycAi\Laravel\External;

use Illuminate\Support\Facades\Http;
use KycAi\Laravel\Contracts\ExternalVerifier;
use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\Results\ExternalVerificationResult;

final class ShuftiExternalVerifier implements ExternalVerifier
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function verify(ExternalVerificationRequest $request): ExternalVerificationResult
    {
        $clientId = $this->config['client_id'] ?? null;
        $secret = $this->config['secret'] ?? null;

        if (! is_string($clientId) || $clientId === '' || ! is_string($secret) || $secret === '') {
            return new ExternalVerificationResult(
                passed: false,
                provider: 'shufti',
                failureReason: 'kyc.external.not_configured',
            );
        }

        $response = Http::withBasicAuth($clientId, $secret)
            ->post(rtrim((string) ($this->config['base_url'] ?? 'https://api.shuftipro.com'), '/').'/status', [
                'reference' => 'kyc-'.$request->nationalId(),
            ]);

        if (! $response->successful()) {
            return new ExternalVerificationResult(
                passed: false,
                provider: 'shufti',
                failureReason: 'kyc.external.provider_error',
                meta: ['status' => $response->status(), 'body' => $response->json()],
            );
        }

        $event = (string) ($response->json('event') ?? '');

        return new ExternalVerificationResult(
            passed: in_array($event, ['verification.accepted', 'request.accepted'], true),
            provider: 'shufti',
            meta: ['event' => $event, 'payload' => $response->json()],
            failureReason: in_array($event, ['verification.accepted', 'request.accepted'], true)
                ? null
                : 'kyc.external.rejected',
        );
    }

    public function sendsDataExternally(): bool
    {
        return true;
    }
}
