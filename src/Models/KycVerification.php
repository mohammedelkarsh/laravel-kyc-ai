<?php

declare(strict_types=1);

namespace KycAi\Laravel\Models;

use Illuminate\Database\Eloquent\Model;

final class KycVerification extends Model
{
    protected $table = 'kyc_verifications';

    protected $fillable = [
        'uuid',
        'user_id',
        'country',
        'national_id',
        'level',
        'status',
        'passed',
        'confidence',
        'extraction_driver',
        'warnings',
        'failure_reason',
        'extracted_fields',
        'internal_meta',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'confidence' => 'float',
        'warnings' => 'array',
        'extracted_fields' => 'array',
        'internal_meta' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function markReviewed(int $reviewerId, bool $approved): void
    {
        $this->forceFill([
            'status' => $approved ? 'passed' : 'failed',
            'passed' => $approved,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
        ])->save();
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }
}
