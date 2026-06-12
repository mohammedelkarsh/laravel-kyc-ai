<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Data\ExtractedDocument;
use PHPUnit\Framework\TestCase;

final class ExtractedDocumentTest extends TestCase
{
    public function test_fields_and_warnings_accessors(): void
    {
        $document = new ExtractedDocument(
            nationalId: '1001244084',
            confidence: 0.88,
            driver: 'fake',
            fields: ['name_en' => 'Ali', 'birth_date' => '1990-01-01'],
            warnings: ['low_confidence'],
        );

        $this->assertSame('1001244084', $document->nationalId());
        $this->assertSame(0.88, $document->confidence());
        $this->assertSame('fake', $document->driver());
        $this->assertSame('Ali', $document->field('name_en'));
        $this->assertNull($document->field('missing'));
        $this->assertSame('1990-01-01', $document->field('birth_date', 'x'));
        $this->assertSame(['low_confidence'], $document->warnings());
    }
}
