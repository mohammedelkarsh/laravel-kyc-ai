<?php

declare(strict_types=1);

namespace KycAi\Laravel\Verifiers;

use KycAi\Laravel\Contracts\InternalVerifier;
use KycAi\Laravel\Exceptions\KycException;

final class InternalVerifierResolver
{
    public function resolve(string $country): InternalVerifier
    {
        return match (strtolower($country)) {
            'sa' => new SaudiInternalVerifier,
            'ae' => $this->resolveUae(),
            'eg' => $this->resolveEgypt(),
            default => throw KycException::unsupportedCountry($country),
        };
    }

    private function resolveUae(): InternalVerifier
    {
        if (! class_exists(\Validators\Ae\EmiratesId::class)) {
            throw KycException::missingCountryPackage('ae', 'validators/ae');
        }

        return new UaeInternalVerifier;
    }

    private function resolveEgypt(): InternalVerifier
    {
        if (! class_exists(\Validators\Eg\EgyptianNationalId::class)) {
            throw KycException::missingCountryPackage('eg', 'validators/eg');
        }

        return new EgyptInternalVerifier;
    }
}
