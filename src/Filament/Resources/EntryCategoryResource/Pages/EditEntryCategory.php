<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource;

class EditEntryCategory extends EditRecord
{
    protected static string $resource = EntryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
