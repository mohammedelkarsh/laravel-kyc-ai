<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests\Support;

use KycAi\Laravel\Contracts\ExternalVerifier;
use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\Results\ExternalVerificationResult;

final class NotConfiguredExternalVerifier implements ExternalVerifier
{
    public function verify(ExternalVerificationRequest $request): ExternalVerificationResult
    {
        return new ExternalVerificationResult(
            passed: false,
            provider: 'test-not-configured',
            failureReason: 'kyc.external.not_configured',
        );
    }

    public function sendsDataExternally(): bool
    {
        return true;
    }
}
