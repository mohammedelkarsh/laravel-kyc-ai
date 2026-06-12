<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Support\NationalIdExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NationalIdExtractorTest extends TestCase
{
    #[DataProvider('saTexts')]
    public function test_extracts_saudi_id_from_text(string $text, ?string $expected): void
    {
        $this->assertSame($expected, NationalIdExtractor::fromText($text, 'sa'));
    }

    #[DataProvider('aeTexts')]
    public function test_extracts_uae_id_from_text(string $text, ?string $expected): void
    {
        $this->assertSame($expected, NationalIdExtractor::fromText($text, 'ae'));
    }

    #[DataProvider('egTexts')]
    public function test_extracts_egypt_id_from_text(string $text, ?string $expected): void
    {
        $this->assertSame($expected, NationalIdExtractor::fromText($text, 'eg'));
    }

    public static function saTexts(): array
    {
        return [
            'plain' => ['ID: 1001244084', '1001244084'],
            'resident' => ['Iqama 2123456789', '2123456789'],
            'no_match' => ['no digits here', null],
            'wrong_length' => ['12345', null],
        ];
    }

    public static function aeTexts(): array
    {
        return [
            'formatted' => ['EID 784-1990-0000000-2', '784199000000002'],
            'plain' => ['784199000000002', '784199000000002'],
            'wrong_prefix' => ['123199000000002', null],
        ];
    }

    public static function egTexts(): array
    {
        return [
            'arabic_context' => ['رقم 29001011234564', '29001011234564'],
            'spaced' => ['2 900 101 123 456 4', '29001011234564'],
            'wrong_century' => ['10201011234567', null],
        ];
    }
}
