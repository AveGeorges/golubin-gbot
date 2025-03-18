<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramLinkResource\Pages;
use App\Filament\Resources\TelegramLinkResource\RelationManagers;
use App\Models\TelegramLink;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;

class TelegramLinkResource extends Resource
{
    protected static ?string $model = TelegramLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('link')
                    ->maxLength(255)
                    ->unique(ignorable: fn($record) => $record)
                    ->required()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Извлекаем только базовую часть ссылки
                        $updatedLink = preg_replace(
                            '/^(?:https?:\/\/)?(?:@)?(?:t\.me\/)?([^\/]+).*$/',
                            't.me/$1', $state
                        );

                        // Устанавливаем обновленное значение
                        $set('link', $updatedLink);
                    }),
                TextInput::make('link_raw')
                    ->maxLength(255),
                Checkbox::make('is_private') // Добавляем чекбокс для закрытых групп
                    ->label('Закрытая группа')
                    ->default(false),
                Select::make('categories')
                    ->multiple()
                    ->relationship(titleAttribute: 'name'),
                TextInput::make('parser_account')
                    ->maxLength(255),
                Checkbox::make('invalid'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('link')
                    ->limit(25)
                    ->copyable()
                    ->searchable(),
                TextColumn::make('link_raw')
                    ->limit(25)
                    ->copyable()
                    ->searchable(),
                IconColumn::make('is_private') // Добавляем иконку для закрытых групп
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->label('Закрытая группа'),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('invalid')
                    ->boolean()
                    ->falseIcon('')
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->sortable(),
            ])
            ->filters([
               Filter::make('is_private')
                  ->label('Закрытые группы')
                  ->query(fn (Builder $query) => $query->where('is_private', true)),
               Filter::make('is_public')
                  ->label('Открытые группы')
                  ->query(fn (Builder $query) => $query->where('is_private', false)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTelegramLinks::route('/'),
            'create' => Pages\CreateTelegramLink::route('/create'),
            'edit' => Pages\EditTelegramLink::route('/{record}/edit'),
        ];
    }
}
