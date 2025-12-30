<?php

namespace Yannelli\EntryVault\Filament\Resources;

use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Yannelli\EntryVault\Filament\EntryVaultPlugin;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages;
use Yannelli\EntryVault\Models\EntryCategory;

class EntryCategoryResource extends Resource
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 2;

    public static function getModel(): string
    {
        return config('entry-vault.models.category', EntryCategory::class);
    }

    public static function getModelLabel(): string
    {
        return config('entry-vault.filament.category_label', 'Category');
    }

    public static function getPluralModelLabel(): string
    {
        return config('entry-vault.filament.category_plural_label', 'Categories');
    }

    public static function getNavigationGroup(): ?string
    {
        return EntryVaultPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        $baseSort = EntryVaultPlugin::get()->getNavigationSort();

        return $baseSort !== null ? $baseSort + 1 : static::$navigationSort;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state) {
                                if (($get('slug') ?? '') !== \Illuminate\Support\Str::slug($old)) {
                                    return;
                                }

                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Appearance')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(50)
                            ->placeholder('heroicon-o-folder')
                            ->helperText('Heroicon name for the category icon'),

                        Forms\Components\ColorPicker::make('color')
                            ->helperText('Color for the category badge'),

                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_system')
                            ->label('System Category')
                            ->helperText('System categories are available to all users and cannot be owned by individuals.'),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Category')
                            ->helperText('Entries without a category will be assigned to the default category.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ownership')
                    ->description('Leave empty for system categories')
                    ->schema([
                        Forms\Components\MorphToSelect::make('owner')
                            ->label('Owner')
                            ->types(static::getOwnerMorphTypes())
                            ->searchable()
                            ->preload(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('icon')
                    ->toggleable(),

                Tables\Columns\ColorColumn::make('color')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('entries_count')
                    ->label('Entries')
                    ->counts('entries')
                    ->sortable(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('System'),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default'),

                Tables\Filters\TrashedFilter::make()
                    ->visible(fn () => config('entry-vault.soft_deletes', true)),
            ])
            ->recordActions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('display_order')
            ->defaultSort('display_order', 'asc');
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Category Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('uuid')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->placeholder('No description'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Appearance')
                    ->schema([
                        Infolists\Components\TextEntry::make('icon')
                            ->placeholder('No icon'),
                        Infolists\Components\ColorEntry::make('color')
                            ->placeholder('No color'),
                        Infolists\Components\TextEntry::make('display_order')
                            ->label('Order'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Settings')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_system')
                            ->label('System Category')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_default')
                            ->label('Default Category')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('entries_count')
                            ->label('Entries')
                            ->state(fn ($record) => $record->entries()->count()),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('deleted_at')
                            ->dateTime()
                            ->placeholder('Not deleted')
                            ->visible(fn () => config('entry-vault.soft_deletes', true)),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntryCategories::route('/'),
            'create' => Pages\CreateEntryCategory::route('/create'),
            'view' => Pages\ViewEntryCategory::route('/{record}'),
            'edit' => Pages\EditEntryCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function getOwnerMorphTypes(): array
    {
        $types = [];

        $userModel = config('entry-vault.user_model');
        if ($userModel && class_exists($userModel)) {
            $types[] = Forms\Components\MorphToSelect\Type::make($userModel)
                ->titleAttribute('name');
        }

        $teamModel = config('entry-vault.team_model');
        if ($teamModel && class_exists($teamModel)) {
            $types[] = Forms\Components\MorphToSelect\Type::make($teamModel)
                ->titleAttribute('name');
        }

        return $types;
    }
}
