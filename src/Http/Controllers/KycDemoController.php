<?php

declare(strict_types=1);

namespace KycAi\Laravel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycLevel;

final class KycDemoController extends Controller
{
    public function create(): View
    {
        return view('kyc::demo', [
            'countries' => [
                'sa' => 'Saudi Arabia',
                'ae' => 'United Arab Emirates',
                'eg' => 'Egypt',
            ],
            'drivers' => ['fake', 'tesseract', 'openai'],
        ]);
    }

    public function store(Request $request, Kyc $kyc): RedirectResponse
    {
        $validated = $request->validate([
            'country' => 'required|in:sa,ae,eg',
            'national_id' => 'nullable|string|max:32',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'extraction_driver' => 'nullable|in:fake,tesseract,openai',
            'match_against' => 'nullable|string|max:32',
        ]);

        $pipeline = $kyc->document($request->file('document'))
            ->country($validated['country'])
            ->level(KycLevel::Standard)
            ->extractWith($validated['extraction_driver'] ?? config('kyc.extraction.default', 'fake'));

        if (! empty($validated['national_id'])) {
            $pipeline = $pipeline->number($validated['national_id']);
        }

        if (! empty($validated['match_against'])) {
            $pipeline = $pipeline->matchAgainst($validated['match_against']);
        }

        $result = $pipeline->verify();

        return back()->with('kyc_result', $result->toArray());
    }
}
