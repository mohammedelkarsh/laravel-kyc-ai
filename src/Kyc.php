<?php

declare(strict_types=1);

namespace KycAi\Laravel;

use KycAi\Laravel\Support\FakeKycState;
use KycAi\Laravel\Support\Testing\FakeKycBuilder;

final class Kyc
{
    public function __construct(
        private readonly KycManager $manager,
        private readonly KycVerifier $verifier,
    ) {}

    public static function make(KycManager $manager, KycVerifier $verifier): self
    {
        return new self($manager, $verifier);
    }

    public function pipeline(): KycPipeline
    {
        return $this->manager->pipeline($this->verifier);
    }

    public function document(mixed $source): KycPipeline
    {
        return $this->pipeline()->document($source);
    }

    public function number(string $value): KycPipeline
    {
        return $this->pipeline()->number($value);
    }

    public function fake(): FakeKycBuilder
    {
        return new FakeKycBuilder;
    }

    public static function resetFakeState(): void
    {
        FakeKycState::reset();
    }
}
