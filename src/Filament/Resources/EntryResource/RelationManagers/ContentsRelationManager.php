<?php

namespace Yannelli\EntryVault\Filament\Resources\EntryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Yannelli\EntryVault\Enums\ContentType;

class ContentsRelationManager extends RelationManager
{
    protected static string $relationship = 'contents';

    protected static ?string $title = 'Content Blocks';

    protected static ?string $recordTitleAttribute = 'type';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options(collect(ContentType::cases())->mapWithKeys(fn ($case) => [
                        $case->value => $case->label(),
                    ]))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('body', null)),

                Forms\Components\MarkdownEditor::make('body')
                    ->label('Content')
                    ->required()
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'markdown'),

                Forms\Components\RichEditor::make('body')
                    ->label('Content')
                    ->required()
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'html'),

                Forms\Components\Textarea::make('body')
                    ->label('Content')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'json')
                    ->helperText('Enter valid JSON content'),

                Forms\Components\Textarea::make('body')
                    ->label('Content')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'text'),

                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull()
                    ->helperText('Optional metadata for this content block'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'markdown' => 'info',
                        'html' => 'warning',
                        'json' => 'success',
                        'text' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ContentType::tryFrom($state)?->label() ?? $state),

                Tables\Columns\TextColumn::make('body')
                    ->label('Preview')
                    ->limit(80)
                    ->wrap(),

                Tables\Columns\TextColumn::make('order')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('type')
                    ->options(collect(ContentType::cases())->mapWithKeys(fn ($case) => [
                        $case->value => $case->label(),
                    ])),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order')
            ->defaultSort('order', 'asc');
    }
}
