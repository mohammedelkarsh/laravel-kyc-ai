<?php

declare(strict_types=1);

namespace KycAi\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class VerifyKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country' => ['required', Rule::in(['sa', 'ae', 'eg'])],
            'national_id' => ['nullable', 'string', 'max:32'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'level' => ['nullable', Rule::in(['internal', 'standard', 'full'])],
            'extraction_driver' => ['nullable', Rule::in(['fake', 'openai', 'tesseract'])],
            'match_against' => ['nullable', 'string', 'max:32'],
        ];
    }
}
