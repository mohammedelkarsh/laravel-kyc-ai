# laravel-kyc-ai — Design notes

## Goal

Bridge **document extraction (AI/OCR)** with **country-specific algorithmic validation** (`validators/*`), with optional external authority checks.

## Layers

```
Document / manual number
        ↓
Extraction driver (optional)     fake | openai | tesseract
        ↓
Internal verifier (default)        validators/sa | ae | eg
        ↓
External verifier (opt-in)         register via ExternalDriverRegistry (e.g. kyc-ai/external-shufti)
        ↓
Audit log (optional) + Events
        ↓
KycResult
```

## Verification levels

| Level | Extraction | Internal | External |
|-------|------------|----------|----------|
| `internal` | No | Yes | No |
| `standard` | Yes | Yes | No |
| `full` | Yes | Yes | Yes |

## Components (v1)

| Component | Status |
|-----------|--------|
| Saudi internal verifier | Done |
| UAE internal verifier | Done (`validators/ae` optional) |
| Egypt internal verifier | Done (`validators/eg` optional) |
| Fake / OpenAI / Tesseract extraction | Done |
| External driver registry | Done (v1.1) |
| Shufti driver package | `kyc-ai/external-shufti` |
| Queue job `ProcessKycDocument` | Done |
| Events `KycVerified` / `KycFailed` | Done |
| Audit model + migration | Done |
| API `POST /api/kyc/verify` | Done (opt-in) |
| Demo UI `/kyc/demo` | Done (opt-in) |
| Filament `KycFilamentPlugin` | Done |

## Privacy model

Each driver implements `sendsDataExternally(): bool`. Warnings are attached to `KycResult` when data leaves the server.

## Manual review

If extraction confidence is below `manual_review_below`, status becomes `pending_review` even when checksum validation passes.

## Tenant Kit integration

Use per-tenant config for `KYC_EXTRACTION_DRIVER` and enable audit in tenant databases after publishing migrations.

## Disclaimer

Internal validation confirms format/checksum only — not government registry verification.
