<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests\Support;

use KycAi\Laravel\Contracts\ExternalVerifier;
use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\Results\ExternalVerificationResult;

final class AcceptingExternalVerifier implements ExternalVerifier
{
    public function verify(ExternalVerificationRequest $request): ExternalVerificationResult
    {
        return new ExternalVerificationResult(
            passed: true,
            provider: 'test-accept',
            meta: ['national_id' => $request->nationalId()],
        );
    }

    public function sendsDataExternally(): bool
    {
        return true;
    }
}
