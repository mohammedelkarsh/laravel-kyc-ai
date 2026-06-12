<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KYC AI Demo</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem auto; max-width: 720px; line-height: 1.5; }
        label { display: block; font-weight: 600; margin-top: 1rem; }
        input, select, button { width: 100%; margin-top: .35rem; padding: .6rem; }
        button { background: #111827; color: #fff; border: 0; cursor: pointer; margin-top: 1.25rem; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: .5rem; padding: 1rem; margin-top: 1.5rem; }
        pre { white-space: pre-wrap; word-break: break-word; font-size: .85rem; }
        .ok { color: #047857; }
        .bad { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>laravel-kyc-ai Demo</h1>
    <p>Upload an ID image. Use the <strong>fake</strong> driver locally (filename can include the ID digits).</p>

    @if (session('kyc_result'))
        @php($result = session('kyc_result'))
        <div class="card">
            <h2 class="{{ ($result['approved'] ?? false) ? 'ok' : 'bad' }}">
                {{ $result['message'] ?? 'Result' }}
            </h2>
            <pre>{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

    <form method="post" action="{{ route('kyc.demo.store') }}" enctype="multipart/form-data">
        @csrf
        <label>Country
            <select name="country" required>
                @foreach ($countries as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>Extraction driver
            <select name="extraction_driver">
                @foreach ($drivers as $driver)
                    <option value="{{ $driver }}" @selected($driver === config('kyc.extraction.default'))>{{ $driver }}</option>
                @endforeach
            </select>
        </label>
        <label>National ID (optional manual entry)
            <input type="text" name="national_id" placeholder="e.g. 1001244084">
        </label>
        <label>Match against (optional)
            <input type="text" name="match_against" placeholder="Must match extracted ID">
        </label>
        <label>Document
            <input type="file" name="document" accept="image/*,.pdf" required>
        </label>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
