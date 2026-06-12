<?php

declare(strict_types=1);

namespace KycAi\Laravel\Support;

final class NationalIdExtractor
{
    public static function fromText(string $text, string $country): ?string
    {
        $digits = preg_replace('/\D+/', ' ', $text) ?? '';
        $candidates = preg_split('/\s+/', trim($digits), flags: PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($candidates as $candidate) {
            $match = self::matchCountryPattern($candidate, $country);

            if ($match !== null) {
                return $match;
            }
        }

        $compact = preg_replace('/\D+/', '', $text) ?? '';

        return self::matchCountryPattern($compact, $country);
    }

    private static function matchCountryPattern(string $value, string $country): ?string
    {
        return match (strtolower($country)) {
            'sa' => preg_match('/^1\d{9}$/', $value) || preg_match('/^2\d{9}$/', $value) ? $value : null,
            'ae' => preg_match('/^784\d{12}$/', $value) ? $value : null,
            'eg' => preg_match('/^[23]\d{13}$/', $value) ? $value : null,
            default => $value !== '' ? $value : null,
        };
    }
}
