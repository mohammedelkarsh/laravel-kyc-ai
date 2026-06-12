<?php

declare(strict_types=1);

namespace KycAi\Laravel\Drivers;

use KycAi\Laravel\Contracts\ExtractionDriver;
use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\Data\ExtractionRequest;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\Support\NationalIdExtractor;
use Symfony\Component\Process\Process;

final class TesseractExtractionDriver implements ExtractionDriver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function extract(ExtractionRequest $request): ExtractedDocument
    {
        $binary = (string) ($this->config['binary'] ?? 'tesseract');
        $language = (string) ($this->config['language'] ?? 'ara+eng');
        $timeout = (int) ($this->config['timeout'] ?? 30);

        $process = new Process([
            $binary,
            $request->document()->path(),
            'stdout',
            '-l',
            $language,
        ]);

        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw KycException::tesseractUnavailable(trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        $text = trim($process->getOutput());
        $nationalId = NationalIdExtractor::fromText($text, $request->country());
        $confidence = $nationalId !== null ? 0.72 : 0.35;

        $warnings = [];

        if ($nationalId === null) {
            $warnings[] = 'no_id_detected';
        }

        return new ExtractedDocument(
            nationalId: $nationalId,
            confidence: $confidence,
            driver: 'tesseract',
            fields: [
                'raw_text' => $text,
            ],
            warnings: $warnings,
        );
    }

    public function sendsDataExternally(): bool
    {
        return false;
    }
}
