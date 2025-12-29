<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource;

class ListEntryCategories extends ListRecords
{
    protected static string $resource = EntryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
