<?php

declare(strict_types=1);

namespace KycAi\Laravel\Exceptions;

use RuntimeException;

final class KycException extends RuntimeException
{
    public static function invalidDocument(string $message): self
    {
        return new self($message);
    }

    public static function unsupportedCountry(string $country): self
    {
        return new self(sprintf('KYC country [%s] is not supported.', $country));
    }

    public static function unknownExtractionDriver(string $driver): self
    {
        return new self(sprintf('KYC extraction driver [%s] is not registered.', $driver));
    }

    public static function unknownExternalDriver(string $driver): self
    {
        return new self(sprintf('KYC external verifier [%s] is not registered.', $driver));
    }

    public static function missingNationalId(): self
    {
        return new self('No national ID was extracted or provided for verification.');
    }

    public static function externalVerificationDisabled(): self
    {
        return new self('External verification was requested but is disabled in configuration.');
    }

    public static function missingCountryPackage(string $country, string $package): self
    {
        return new self(sprintf(
            'Country [%s] requires composer package [%s]. Run: composer require %s',
            $country,
            $package,
            $package,
        ));
    }

    public static function tesseractUnavailable(string $message): self
    {
        return new self('Tesseract OCR is unavailable: '.$message);
    }
}
