# Changelog

All notable changes to `kyc-ai/laravel` will be documented in this file.

## [1.1.0] - 2026-06-12

### Added

- `ExternalDriverRegistry` for satellite external verification packages
- `ExternalVerificationRequest` now includes optional `DocumentSource` for provider APIs

### Changed

- Removed built-in Shufti stub — use `kyc-ai/external-shufti` instead
- External driver credentials no longer ship in default `config/kyc.php`

### Migration

```bash
composer require kyc-ai/external-shufti
php artisan vendor:publish --tag=kyc-shufti-config
```

## [1.0.0] - 2026-06-12

### Added

- KYC pipeline: extraction (fake, OpenAI, Tesseract) + internal verification (SA/AE/EG)
- Optional external verification (Shufti stub)
- Verification levels: `internal`, `standard`, `full`
- Laravel validation rule, facade, queue job, events
- Audit log model and migration
- Opt-in API and demo routes
- Filament plugin for manual review
- 119 automated tests
