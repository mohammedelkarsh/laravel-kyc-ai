<?php

declare(strict_types=1);

namespace KycAi\Laravel\Support\Testing;

use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\Support\FakeKycState;

final class FakeKycBuilder
{
    public function willExtract(ExtractedDocument $document): self
    {
        FakeKycState::instance()->willExtract($document);

        return $this;
    }

    public function willExtractId(string $nationalId, float $confidence = 0.98): self
    {
        return $this->willExtract(new ExtractedDocument(
            nationalId: $nationalId,
            confidence: $confidence,
            driver: 'fake',
        ));
    }
}
