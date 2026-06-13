# laravel-kyc-ai

[![Tests](https://github.com/mohammedelkarsh/laravel-kyc-ai/actions/workflows/tests.yml/badge.svg)](https://github.com/mohammedelkarsh/laravel-kyc-ai/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Laravel package for **KYC workflows**: extract identity fields from documents (AI/OCR), verify with country-specific [validators](https://packagist.org/packages/validators/sa) packages, and optionally call external providers.

> **v1.1** · SA / AE / EG · fake · OpenAI · Tesseract · external drivers · Queue · API · Demo UI · Filament review

---

## Install

```bash
composer require kyc-ai/laravel

# Optional countries
composer require validators/ae validators/eg

php artisan vendor:publish --tag=kyc-config
```

---

## Quick start

### Internal only (no AI)

```php
use KycAi\Laravel\Facades\Kyc;
use KycAi\Laravel\KycLevel;

$result = Kyc::number('1001244084')
    ->country('sa')
    ->level(KycLevel::Internal)
    ->verify();
```

### Document + extraction + internal verify

```php
$result = Kyc::document($request->file('id_front'))
    ->country('sa')
    ->extractWith('fake') // fake | tesseract | openai
    ->matchAgainst($request->input('national_id'))
    ->verify();

$result->approved();
$result->needsManualReview();
$result->toArray();
```

### Async queue

```php
Kyc::document($file)
    ->country('sa')
    ->forUser($user->id)
    ->dispatch();
```

### Validation rule

```php
use KycAi\Laravel\Rules\KycDocument;

'id_front' => ['required', 'image', new KycDocument(country: 'sa', matchField: 'national_id')],
```

---

## Verification levels

| Level | Extraction | Internal | External |
|-------|------------|----------|----------|
| `internal` | No | Yes | No |
| `standard` | Yes | Yes | No |
| `full` | Yes | Yes | Yes (opt-in) |

---

## Drivers

| Extraction | Sends data outside? |
|------------|---------------------|
| `fake` | No |
| `tesseract` | No |
| `openai` | Yes |

| External | Sends data outside? | Package |
|----------|---------------------|---------|
| `shufti` | Yes (requires API keys) | [`kyc-ai/external-shufti`](https://packagist.org/packages/kyc-ai/external-shufti) |

```env
KYC_EXTRACTION_DRIVER=fake
OPENAI_API_KEY=
TESSERACT_BINARY=tesseract
KYC_EXTERNAL_ENABLED=false
KYC_EXTERNAL_DRIVER=shufti
```

Install Shufti driver:

```bash
composer require kyc-ai/external-shufti
php artisan vendor:publish --tag=kyc-shufti-config
```

---

## Demo UI

```env
KYC_ROUTES_DEMO=true
```

See [demo/README.md](demo/README.md).

---

## API

```env
KYC_ROUTES_API=true
```

`POST /api/kyc/verify` with `country`, optional `national_id`, optional `document`.

---

## Audit log + Filament

```bash
php artisan vendor:publish --tag=kyc-migrations
php artisan migrate
```

```env
KYC_AUDIT_ENABLED=true
```

```php
use KycAi\Laravel\Filament\KycFilamentPlugin;

$panel->plugin(KycFilamentPlugin::make());
```

---

## Development

```bash
composer install
composer test          # 119 tests
```

Monorepo setup: [docs/TESTING.md](docs/TESTING.md).

Architecture: [docs/DESIGN.md](docs/DESIGN.md).

---

## Disclaimer

Internal validation checks **format and checksum** only — not government registry verification.

## License

MIT — see [LICENSE](LICENSE).
