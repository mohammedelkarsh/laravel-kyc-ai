# Changelog

All notable changes to `kyc-ai/laravel` will be documented in this file.

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
