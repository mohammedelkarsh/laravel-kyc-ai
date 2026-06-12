<?php

declare(strict_types=1);

namespace KycAi\Laravel\Drivers;

use KycAi\Laravel\Contracts\ExtractionDriver;
use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\Data\ExtractionRequest;
use KycAi\Laravel\Support\CountryFakeIds;
use KycAi\Laravel\Support\FakeKycState;

final class FakeExtractionDriver implements ExtractionDriver
{
    public function extract(ExtractionRequest $request): ExtractedDocument
    {
        if ($state = FakeKycState::instance()->nextExtraction()) {
            return $state;
        }

        $name = $request->document()->originalName() ?? '';
        $nationalId = CountryFakeIds::fromFilename($name, $request->country())
            ?? CountryFakeIds::nationalId($request->country());

        return new ExtractedDocument(
            nationalId: $nationalId,
            confidence: 0.98,
            driver: 'fake',
            fields: [
                'name_ar' => 'محمد أحمد',
                'name_en' => 'Mohammed Ahmed',
                'birth_date' => '1990-01-15',
                'expiry_date' => '2030-06-01',
            ],
        );
    }

    public function sendsDataExternally(): bool
    {
        return false;
    }
}
