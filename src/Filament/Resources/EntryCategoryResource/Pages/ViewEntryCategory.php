<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource;

class ViewEntryCategory extends ViewRecord
{
    protected static string $resource = EntryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
