<?php

declare(strict_types=1);

namespace KycAi\Laravel\Verifiers;

use KycAi\Laravel\Contracts\InternalVerifier;
use KycAi\Laravel\Results\InternalVerificationResult;
use Validators\Core\ValidationResult;

abstract class AbstractInternalVerifier implements InternalVerifier
{
    abstract protected function validate(string $nationalId): ValidationResult;

    public function verify(string $nationalId, ?string $matchAgainst = null): InternalVerificationResult
    {
        $result = $this->validate($nationalId);

        if ($matchAgainst !== null) {
            $normalizedMatch = $this->validate($matchAgainst)->normalized();

            if ($result->isValid() && $result->normalized() !== $normalizedMatch) {
                return new InternalVerificationResult(
                    passed: false,
                    nationalId: $result->normalized(),
                    errors: ['kyc.match.mismatch'],
                    metadata: $result->meta(),
                    matchFailure: 'kyc.match.mismatch',
                );
            }
        }

        if ($result->isValid()) {
            return new InternalVerificationResult(
                passed: true,
                nationalId: $result->normalized(),
                metadata: $result->meta(),
            );
        }

        return new InternalVerificationResult(
            passed: false,
            nationalId: $result->normalized(),
            errors: [$result->errorKey() ?? 'kyc.internal.invalid'],
            metadata: $result->meta(),
        );
    }
}
