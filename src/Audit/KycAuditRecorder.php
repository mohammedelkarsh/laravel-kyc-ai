<?php

declare(strict_types=1);

namespace KycAi\Laravel\Audit;

use Illuminate\Support\Str;
use KycAi\Laravel\Models\KycVerification;
use KycAi\Laravel\Results\KycResult;

final class KycAuditRecorder
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function record(KycResult $result, ?int $userId = null): ?KycVerification
    {
        if (! ($this->config['audit']['enabled'] ?? false)) {
            return null;
        }

        if (! class_exists(KycVerification::class)) {
            return null;
        }

        return KycVerification::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'country' => $result->country(),
            'national_id' => $result->nationalId(),
            'level' => $result->level()->value,
            'status' => $result->status(),
            'passed' => $result->passed(),
            'confidence' => $result->confidence(),
            'extraction_driver' => $result->extraction()?->driver(),
            'warnings' => $result->warnings(),
            'failure_reason' => $result->failureReason(),
            'extracted_fields' => $result->extraction()?->fields() ?? [],
            'internal_meta' => $result->internal()?->meta() ?? [],
        ]);
    }
}
