<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;
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
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading(fn (Entry $record): string => "Preview: {$record->title}")
                ->modalContent(fn (Entry $record): View => view('entry-vault::filament.entry-preview', [
                    'contents' => $record->contents,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->slideOver(),
            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Entry $record): bool => $record->state instanceof Draft)
                ->action(fn (Entry $record): void => $record->publish()),
            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Entry $record): bool => $record->state instanceof Published)
                ->action(fn (Entry $record): void => $record->unpublish()),
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Entry $record): bool => ! $record->state instanceof Archived)
                ->action(fn (Entry $record): void => $record->archive()),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
