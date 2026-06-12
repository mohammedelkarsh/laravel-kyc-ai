# Laravel Tenant Kit integration

Use KYC inside tenant context with per-workspace settings.

## Per-tenant driver

```php
$tenant->run(function () use ($user, $file) {
    config(['kyc.extraction.default' => 'tesseract']);

    $result = Kyc::document($file)
        ->country('sa')
        ->forUser($user->id)
        ->verify();
});
```

## Audit in tenant DB

1. Publish migrations inside tenant migrations path (Stancl).
2. Enable `KYC_AUDIT_ENABLED=true` in tenant `.env`.
3. Register `KycFilamentPlugin` on the workspace Filament panel for review queues.

## Queue

Use tenant-aware queues so `ProcessKycDocument` runs in the correct tenant context (Stancl `Tenant::run` in job middleware or dispatch from within `$tenant->run()`).
