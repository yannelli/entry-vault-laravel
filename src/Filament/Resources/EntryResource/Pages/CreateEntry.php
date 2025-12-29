<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Yannelli\EntryVault\Filament\Resources\EntryResource;

class CreateEntry extends CreateRecord
{
    protected static string $resource = EntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->check()) {
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
        }

        return $data;
    }
}
