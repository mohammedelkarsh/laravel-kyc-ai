<?php

declare(strict_types=1);

namespace KycAi\Laravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use KycAi\Laravel\Data\KycRequestData;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\KycVerifier;
use KycAi\Laravel\Results\KycResult;

final class ProcessKycDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
    ) {}

    public static function fromData(KycRequestData $data): self
    {
        return new self($data->toArray());
    }

    public function handle(KycManager $manager, KycVerifier $verifier): KycResult
    {
        $data = KycRequestData::fromArray($this->payload);

        return $verifier->verifyPipeline($data->toPipeline($manager, $verifier));
    }
}
