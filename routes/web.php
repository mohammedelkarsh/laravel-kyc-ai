<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use KycAi\Laravel\Http\Controllers\KycDemoController;

Route::middleware('web')->prefix('kyc')->group(function (): void {
    Route::get('demo', [KycDemoController::class, 'create'])->name('kyc.demo');
    Route::post('demo', [KycDemoController::class, 'store'])->name('kyc.demo.store');
});
