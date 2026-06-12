<?php

declare(strict_types=1);

namespace KycAi\Laravel\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use KycAi\Laravel\Contracts\ExtractionDriver;
use KycAi\Laravel\Data\ExtractedDocument;
use KycAi\Laravel\Data\ExtractionRequest;
use KycAi\Laravel\Exceptions\KycException;

final class OpenAiVisionDriver implements ExtractionDriver
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly ?PendingRequest $http = null,
    ) {}

    public function extract(ExtractionRequest $request): ExtractedDocument
    {
        $apiKey = $this->config['api_key'] ?? null;

        if (! is_string($apiKey) || $apiKey === '') {
            throw KycException::invalidDocument('OpenAI API key is not configured.');
        }

        $document = $request->document();
        $mimeType = $document->mimeType() ?? 'image/jpeg';
        $imageUrl = sprintf('data:%s;base64,%s', $mimeType, $document->base64());

        $prompt = match ($request->country()) {
            'sa' => 'Extract Saudi national ID card fields. Return JSON only with keys: national_id (10 digits), name_ar, name_en, birth_date (YYYY-MM-DD), expiry_date (YYYY-MM-DD), confidence (0-1).',
            'ae' => 'Extract UAE Emirates ID card fields. Return JSON only with keys: national_id (15 digits, may include 784 prefix), name_ar, name_en, birth_date (YYYY-MM-DD), expiry_date (YYYY-MM-DD), nationality, confidence (0-1).',
            'eg' => 'Extract Egyptian national ID card fields. Return JSON only with keys: national_id (14 digits), name_ar, name_en, birth_date (YYYY-MM-DD), confidence (0-1).',
            default => 'Extract national ID document fields. Return JSON only with keys: national_id, name_ar, name_en, birth_date, expiry_date, confidence (0-1).',
        };

        $response = $this->http()->withToken($apiKey)->post(
            rtrim((string) ($this->config['base_url'] ?? 'https://api.openai.com/v1'), '/').'/chat/completions',
            [
                'model' => $this->config['model'] ?? 'gpt-4o',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                        ],
                    ],
                ],
            ],
        );

        if (! $response->successful()) {
            throw KycException::invalidDocument('OpenAI extraction failed: '.$response->body());
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw KycException::invalidDocument('OpenAI returned an empty extraction payload.');
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        $nationalId = isset($payload['national_id']) ? preg_replace('/\D+/', '', (string) $payload['national_id']) : null;
        $confidence = (float) ($payload['confidence'] ?? 0.0);

        $warnings = [];

        if ($confidence < 0.75) {
            $warnings[] = 'low_confidence';
        }

        return new ExtractedDocument(
            nationalId: $nationalId !== '' ? $nationalId : null,
            confidence: $confidence,
            driver: 'openai',
            fields: [
                'name_ar' => $payload['name_ar'] ?? null,
                'name_en' => $payload['name_en'] ?? null,
                'birth_date' => $payload['birth_date'] ?? null,
                'expiry_date' => $payload['expiry_date'] ?? null,
            ],
            warnings: $warnings,
        );
    }

    public function sendsDataExternally(): bool
    {
        return true;
    }

    private function http(): PendingRequest
    {
        return $this->http ?? Http::timeout(60);
    }
}
