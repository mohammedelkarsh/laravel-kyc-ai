<?php

declare(strict_types=1);

namespace KycAi\Laravel\Support;

use KycAi\Laravel\Exceptions\KycException;
use Validators\Sa\SaudiNationalId;

final class CountryFakeIds
{
    public static function nationalId(string $country): string
    {
        return match (strtolower($country)) {
            'sa' => SaudiNationalId::fake(),
            'ae' => self::ae(),
            'eg' => self::eg(),
            default => throw KycException::unsupportedCountry($country),
        };
    }

    private static function ae(): string
    {
        if (! class_exists(\Validators\Ae\EmiratesId::class)) {
            throw KycException::missingCountryPackage('ae', 'validators/ae');
        }

        return \Validators\Ae\EmiratesId::fake();
    }

    private static function eg(): string
    {
        if (! class_exists(\Validators\Eg\EgyptianNationalId::class)) {
            throw KycException::missingCountryPackage('eg', 'validators/eg');
        }

        return \Validators\Eg\EgyptianNationalId::fake();
    }

    public static function fromFilename(string $filename, string $country): ?string
    {
        $patterns = match (strtolower($country)) {
            'sa' => '/(?:^|[^\d])(1\d{9}|2\d{9})(?:[^\d]|$)/',
            'ae' => '/(?:^|[^\d])(784\d{12})(?:[^\d]|$)/',
            'eg' => '/(?:^|[^\d])([23]\d{13})(?:[^\d]|$)/',
            default => '/(?:^|[^\d])(\d{10,15})(?:[^\d]|$)/',
        };

        if (preg_match($patterns, $filename, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
