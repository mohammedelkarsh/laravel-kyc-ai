# KYC AI Demo UI

Enable the built-in demo without a separate Laravel app.

## Setup in your Laravel project

```bash
composer require kyc-ai/laravel
php artisan vendor:publish --tag=kyc-config
```

`.env`:

```env
KYC_ROUTES_DEMO=true
KYC_EXTRACTION_DRIVER=fake
```

Visit: `http://your-app.test/kyc/demo`

## Tips

- With the **fake** driver, name the file with the national ID digits (e.g. `1001244084-id.jpg`).
- Use **tesseract** for fully local OCR (install Tesseract + Arabic language pack).
- Use **openai** when `OPENAI_API_KEY` is set.

## API

```env
KYC_ROUTES_API=true
```

```bash
curl -X POST http://your-app.test/api/kyc/verify \
  -F country=sa \
  -F national_id=1001244084 \
  -F document=@id.jpg
```

## Filament manual review

```php
// app/Providers/Filament/AdminPanelProvider.php
use KycAi\Laravel\Filament\KycFilamentPlugin;

$panel->plugin(KycFilamentPlugin::make());
```

```bash
php artisan vendor:publish --tag=kyc-migrations
php artisan migrate
```

```env
KYC_AUDIT_ENABLED=true
```
