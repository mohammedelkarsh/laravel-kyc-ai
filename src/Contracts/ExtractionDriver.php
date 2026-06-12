<?php

declare(strict_types=1);

namespace KycAi\Laravel\Contracts;

use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\Data\ExtractionRequest;

interface ExtractionDriver
{
    public function extract(ExtractionRequest $request): ExtractedDocument;

    public function sendsDataExternally(): bool;
}
