<?php

declare(strict_types=1);

namespace KycAi\Laravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycLevel;
use KycAi\Laravel\Results\KycResult;

final class KycDocument implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private ?KycResult $lastResult = null;

    public function __construct(
        private readonly string $country = 'sa',
        private readonly KycLevel|string $level = KycLevel::Standard,
        private readonly ?string $matchField = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pipeline = app(Kyc::class)
            ->document($value)
            ->country($this->country)
            ->level($this->level);

        if ($this->matchField !== null) {
            $matchValue = $this->data[$this->matchField] ?? null;

            if (is_string($matchValue) && $matchValue !== '') {
                $pipeline = $pipeline->matchAgainst($matchValue);
            }
        }

        $result = $pipeline->verify();
        $this->lastResult = $result;

        if ($result->passed()) {
            return;
        }

        $fail($result->userMessage());
    }

    public function lastResult(): ?KycResult
    {
        return $this->lastResult;
    }
}
