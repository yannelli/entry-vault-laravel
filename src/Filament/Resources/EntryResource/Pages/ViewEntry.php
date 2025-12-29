<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Yannelli\EntryVault\Filament\Resources\EntryResource;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Archived;
use Yannelli\EntryVault\States\Draft;
use Yannelli\EntryVault\States\Published;

class ViewEntry extends ViewRecord
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Entry $record): bool => $record->state instanceof Draft)
                ->action(function (Entry $record): void {
                    $record->state->transitionTo(Published::class);
                    $record->published_at = now();
                    $record->save();
                }),
            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Entry $record): bool => $record->state instanceof Published)
                ->action(function (Entry $record): void {
                    $record->state->transitionTo(Draft::class);
                    $record->save();
                }),
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Entry $record): bool => ! $record->state instanceof Archived)
                ->action(function (Entry $record): void {
                    $record->state->transitionTo(Archived::class);
                    $record->save();
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
