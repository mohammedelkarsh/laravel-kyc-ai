<?php

declare(strict_types=1);

namespace KycAi\Laravel\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use KycAi\Laravel\Filament\Resources\KycVerificationResource\Pages\ListKycVerifications;
use KycAi\Laravel\Models\KycVerification;

final class KycVerificationResource extends Resource
{
    protected static ?string $model = KycVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'KYC';

    protected static ?string $navigationLabel = 'Verifications';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('country')->badge(),
                Tables\Columns\TextColumn::make('national_id')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\IconColumn::make('passed')->boolean(),
                Tables\Columns\TextColumn::make('confidence'),
                Tables\Columns\TextColumn::make('extraction_driver'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_review' => 'Pending review',
                        'passed' => 'Passed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->visible(fn (KycVerification $record): bool => $record->status === 'pending_review')
                    ->requiresConfirmation()
                    ->action(fn (KycVerification $record) => $record->markReviewed(auth()->id() ?? 0, true)),
                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->visible(fn (KycVerification $record): bool => $record->status === 'pending_review')
                    ->requiresConfirmation()
                    ->action(fn (KycVerification $record) => $record->markReviewed(auth()->id() ?? 0, false)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKycVerifications::route('/'),
        ];
    }
}
