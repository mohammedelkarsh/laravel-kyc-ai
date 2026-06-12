<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\KycLevel;
use PHPUnit\Framework\Attributes\DataProvider;

final class KycLevelTest extends TestCase
{
    #[DataProvider('levelExpectations')]
    public function test_level_requirements(KycLevel $level, bool $extraction, bool $external): void
    {
        $this->assertSame($extraction, $level->requiresExtraction());
        $this->assertSame($external, $level->requiresExternal());
    }

    public static function levelExpectations(): array
    {
        return [
            'internal' => [KycLevel::Internal, false, false],
            'standard' => [KycLevel::Standard, true, false],
            'full' => [KycLevel::Full, true, true],
        ];
    }

    public function test_level_from_string(): void
    {
        $this->assertSame(KycLevel::Standard, KycLevel::from('standard'));
    }
}
