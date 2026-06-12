<?php

declare(strict_types=1);

namespace KycAi\Laravel\Data;

use Illuminate\Contracts\Support\Arrayable;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\KycPipeline;
use KycAi\Laravel\KycVerifier;

/**
 * @implements Arrayable<string, mixed>
 */
final class KycRequestData implements Arrayable
{
    public function __construct(
        public readonly ?string $documentPath = null,
        public readonly ?string $nationalId = null,
        public readonly string $country = 'sa',
        public readonly ?string $level = null,
        public readonly ?string $extractionDriver = null,
        public readonly ?string $externalDriver = null,
        public readonly ?string $matchAgainst = null,
        public readonly bool $deleteAfterVerify = false,
        public readonly ?int $userId = null,
    ) {}

    public function toPipeline(KycManager $manager, KycVerifier $verifier): KycPipeline
    {
        $pipeline = $manager->pipeline($verifier)->country($this->country);

        if ($this->documentPath !== null) {
            $pipeline = $pipeline->document($this->documentPath);
        }

        if ($this->nationalId !== null) {
            $pipeline = $pipeline->number($this->nationalId);
        }

        if ($this->level !== null) {
            $pipeline = $pipeline->level(KycLevel::from($this->level));
        }

        if ($this->extractionDriver !== null) {
            $pipeline = $pipeline->extractWith($this->extractionDriver);
        }

        if ($this->externalDriver !== null) {
            $pipeline = $pipeline->verifyWith($this->externalDriver);
        }

        if ($this->matchAgainst !== null) {
            $pipeline = $pipeline->matchAgainst($this->matchAgainst);
        }

        if ($this->deleteAfterVerify) {
            $pipeline = $pipeline->deleteAfterVerify();
        }

        return $pipeline;
    }

  /**
   * @return array<string, mixed>
   */
    public function toArray(): array
    {
        return [
            'document_path' => $this->documentPath,
            'national_id' => $this->nationalId,
            'country' => $this->country,
            'level' => $this->level,
            'extraction_driver' => $this->extractionDriver,
            'external_driver' => $this->externalDriver,
            'match_against' => $this->matchAgainst,
            'delete_after_verify' => $this->deleteAfterVerify,
            'user_id' => $this->userId,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            documentPath: isset($data['document_path']) ? (string) $data['document_path'] : null,
            nationalId: isset($data['national_id']) ? (string) $data['national_id'] : null,
            country: (string) ($data['country'] ?? 'sa'),
            level: isset($data['level']) ? (string) $data['level'] : null,
            extractionDriver: isset($data['extraction_driver']) ? (string) $data['extraction_driver'] : null,
            externalDriver: isset($data['external_driver']) ? (string) $data['external_driver'] : null,
            matchAgainst: isset($data['match_against']) ? (string) $data['match_against'] : null,
            deleteAfterVerify: (bool) ($data['delete_after_verify'] ?? false),
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
        );
    }
}
