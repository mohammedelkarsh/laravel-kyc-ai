<?php

declare(strict_types=1);

namespace KycAi\Laravel\Support;

use KycAi\Laravel\Data\ExtractedDocument;

final class FakeKycState
{
    private static ?self $instance = null;

    private ?ExtractedDocument $nextExtraction = null;

    public static function instance(): self
    {
        return self::$instance ??= new self;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function willExtract(ExtractedDocument $document): void
    {
        $this->nextExtraction = $document;
    }

    public function nextExtraction(): ?ExtractedDocument
    {
        $document = $this->nextExtraction;
        $this->nextExtraction = null;

        return $document;
    }
}
