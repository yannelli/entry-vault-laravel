<?php

namespace Yannelli\EntryVault\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Yannelli\EntryVault\Enums\EntryVisibility;
use Yannelli\EntryVault\Filament\EntryVaultPlugin;
use Yannelli\EntryVault\Filament\Resources\EntryResource\Pages;
use Yannelli\EntryVault\Filament\Resources\EntryResource\RelationManagers\ContentsRelationManager;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Archived;
use Yannelli\EntryVault\States\Draft;
use Yannelli\EntryVault\States\Published;

class EntryResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return config('entry-vault.models.entry', Entry::class);
    }

    public static function getModelLabel(): string
    {
        return config('entry-vault.filament.entry_label', 'Entry');
    }

    public static function getPluralModelLabel(): string
    {
        return config('entry-vault.filament.entry_plural_label', 'Entries');
    }

    public static function getNavigationGroup(): ?string
    {
        return EntryVaultPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return EntryVaultPlugin::get()->getNavigationSort() ?? static::$navigationSort;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Entry Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
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
                            ->maxLength(65535),

                        Forms\Components\TagsInput::make('keywords')
                            ->separator(',')
                            ->splitKeys(['Tab', ',']),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Classification')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(65535),
                                Forms\Components\TextInput::make('icon')
                                    ->maxLength(50),
                                Forms\Components\ColorPicker::make('color'),
                            ]),

                        Forms\Components\Select::make('visibility')
                            ->options(collect(EntryVisibility::cases())->mapWithKeys(fn ($case) => [
                                $case->value => $case->label(),
                            ]))
                            ->default(config('entry-vault.default_visibility', 'private'))
                            ->required(),

                        Forms\Components\Select::make('state')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default(config('entry-vault.default_state', 'draft'))
                            ->required(),

                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Template Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_template')
                            ->label('This is a template')
                            ->helperText('Templates can be used to create new entries with pre-filled content.'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Featured entries/templates are highlighted in listings.'),

                        Forms\Components\Select::make('template_id')
                            ->label('Created from template')
                            ->relationship('template', 'title', fn (Builder $query) => $query->where('is_template', true))
                            ->searchable()
                            ->preload()
                            ->disabled(fn (?Entry $record) => $record !== null),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Ownership')
                    ->schema([
                        Forms\Components\MorphToSelect::make('owner')
                            ->label('Owner')
                            ->types(static::getOwnerMorphTypes())
                            ->searchable()
                            ->preload(),

                        Forms\Components\MorphToSelect::make('team')
                            ->label('Team')
                            ->types(static::getTeamMorphTypes())
                            ->searchable()
                            ->preload()
                            ->visible(fn () => config('entry-vault.team_model') !== null),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->color(fn (Entry $record): string => match (true) {
                        $record->state instanceof Draft => 'gray',
                        $record->state instanceof Published => 'success',
                        $record->state instanceof Archived => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (Entry $record): string => $record->state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('visibility')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'team' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => EntryVisibility::tryFrom($state)?->label() ?? $state)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_template')
                    ->label('Template')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('visibility')
                    ->options(collect(EntryVisibility::cases())->mapWithKeys(fn ($case) => [
                        $case->value => $case->label(),
                    ])),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_template')
                    ->label('Template'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\TrashedFilter::make()
                    ->visible(fn () => config('entry-vault.soft_deletes', true)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('preview')
                        ->label('Preview')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn (Entry $record): string => "Preview: {$record->title}")
                        ->modalContent(fn (Entry $record): \Illuminate\Contracts\View\View => view('entry-vault::filament.entry-preview', [
                            'contents' => $record->contents,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->slideOver(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('publish')
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
                    Tables\Actions\Action::make('unpublish')
                        ->label('Unpublish')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (Entry $record): bool => $record->state instanceof Published)
                        ->action(function (Entry $record): void {
                            $record->state->transitionTo(Draft::class);
                            $record->save();
                        }),
                    Tables\Actions\Action::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Entry $record): bool => ! $record->state instanceof Archived)
                        ->action(function (Entry $record): void {
                            $record->state->transitionTo(Archived::class);
                            $record->save();
                        }),
                    Tables\Actions\Action::make('restore_state')
                        ->label('Restore to Draft')
                        ->icon('heroicon-o-arrow-path')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn (Entry $record): bool => $record->state instanceof Archived)
                        ->action(function (Entry $record): void {
                            $record->state->transitionTo(Draft::class);
                            $record->save();
                        }),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_order', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Entry Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('uuid')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('keywords')
                            ->badge()
                            ->separator(','),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Classification')
                    ->schema([
                        Infolists\Components\TextEntry::make('category.name')
                            ->label('Category')
                            ->badge(),
                        Infolists\Components\TextEntry::make('state')
                            ->badge()
                            ->color(fn (Entry $record): string => match (true) {
                                $record->state instanceof Draft => 'gray',
                                $record->state instanceof Published => 'success',
                                $record->state instanceof Archived => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (Entry $record): string => $record->state->label()),
                        Infolists\Components\TextEntry::make('visibility')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'public' => 'success',
                                'private' => 'gray',
                                'team' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => EntryVisibility::tryFrom($state)?->label() ?? $state),
                        Infolists\Components\TextEntry::make('display_order')
                            ->label('Order'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Template Information')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_template')
                            ->label('Is Template')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('template.title')
                            ->label('Created from Template')
                            ->placeholder('Not from template'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('published_at')
                            ->dateTime()
                            ->placeholder('Not published'),
                        Infolists\Components\TextEntry::make('deleted_at')
                            ->dateTime()
                            ->placeholder('Not deleted')
                            ->visible(fn () => config('entry-vault.soft_deletes', true)),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Content Preview')
                    ->schema([
                        Infolists\Components\ViewEntry::make('contents')
                            ->view('entry-vault::filament.entry-preview')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ContentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntries::route('/'),
            'create' => Pages\CreateEntry::route('/create'),
            'view' => Pages\ViewEntry::route('/{record}'),
            'edit' => Pages\EditEntry::route('/{record}/edit'),
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

        return $types;
    }

    protected static function getTeamMorphTypes(): array
    {
        $types = [];

        $teamModel = config('entry-vault.team_model');
        if ($teamModel && class_exists($teamModel)) {
            $types[] = Forms\Components\MorphToSelect\Type::make($teamModel)
                ->titleAttribute('name');
        }

        return $types;
    }
}
