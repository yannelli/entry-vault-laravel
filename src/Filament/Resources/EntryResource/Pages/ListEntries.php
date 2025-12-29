<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Yannelli\EntryVault\Filament\Resources\EntryResource;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
