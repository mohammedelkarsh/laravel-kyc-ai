<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use KycAi\Laravel\Http\Controllers\KycVerificationController;

Route::prefix('api/kyc')->group(function (): void {
    Route::post('verify', [KycVerificationController::class, 'store'])->name('kyc.api.verify');
});
