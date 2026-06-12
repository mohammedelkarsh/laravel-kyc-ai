<?php

declare(strict_types=1);

return [
    'passed' => 'Identity verification passed.',
    'failed' => 'Identity verification failed.',
    'pending_review' => 'Verification requires manual review due to low confidence.',
    'match.mismatch' => 'The extracted national ID does not match the provided value.',
    'internal.invalid' => 'The national ID failed internal validation.',
    'external.not_configured' => 'External verification is not configured.',
    'external.provider_error' => 'External verification provider returned an error.',
    'external.rejected' => 'External verification was rejected.',
];
