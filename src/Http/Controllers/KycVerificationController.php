<?php

declare(strict_types=1);

namespace KycAi\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use KycAi\Laravel\Http\Requests\VerifyKycRequest;
use KycAi\Laravel\Kyc;
use KycAi\Laravel\KycLevel;

final class KycVerificationController extends Controller
{
    public function __construct(
        private readonly Kyc $kyc,
    ) {}

    public function store(VerifyKycRequest $request): JsonResponse
    {
        $pipeline = $this->kyc->pipeline()->country((string) $request->input('country'));

        if ($request->hasFile('document')) {
            $pipeline = $pipeline->document($request->file('document'));
        }

        if ($request->filled('national_id')) {
            $pipeline = $pipeline->number((string) $request->input('national_id'));
        }

        if ($request->filled('level')) {
            $pipeline = $pipeline->level(KycLevel::from((string) $request->input('level')));
        } elseif (! $request->hasFile('document')) {
            $pipeline = $pipeline->level(KycLevel::Internal);
        }

        if ($request->filled('extraction_driver')) {
            $pipeline = $pipeline->extractWith((string) $request->input('extraction_driver'));
        }

        if ($request->filled('match_against')) {
            $pipeline = $pipeline->matchAgainst((string) $request->input('match_against'));
        }

        if ($request->user()) {
            $pipeline = $pipeline->forUser((int) $request->user()->getAuthIdentifier());
        }

        $result = $pipeline->verify();

        return response()->json($result->toArray(), $result->approved() ? 200 : 422);
    }
}
