<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\Resources\TelegramLinkResource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TelegramLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'telegram_links';

    protected static ?string $recordTitleAttribute = 'link';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('link')
                    ->required()
                    ->unique()
                    ->maxLength(255)
                    ->afterStateUpdated(function (callable $set, $state) {
                        $updatedLink = preg_replace(
                            '/^(?:https?:\/\/)?(?:@)?(?:t\.me\/)?([^\/]+).*$/',
                            't.me/$1', $state
                        );

                        $set('link', $updatedLink);
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('link')
            ->columns([
                TextColumn::make('link')
                    ->limit(25)
                    ->copyable()
                    ->searchable(),
                TextColumn::make('link_raw')
                    ->limit(25)
                    ->copyable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('parser_account')
                    ->sortable(),
                Tables\Columns\IconColumn::make('invalid')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                 Tables\Actions\CreateAction::make(),
//                 Tables\Actions\AttachAction::make(),
                Tables\Actions\Action::make('createOrAttach')
                ->label('Create or Attach')
                ->form([
                    TextInput::make('link')
                        ->required()
                        ->label('Telegram Link')
                        ->placeholder('Enter a Telegram link...')
                ])
                ->action(function (array $data) {
                    $link = preg_replace(
                        '/^(?:https?:\/\/)?(?:@)?(?:t\.me\/)?([^\/]+).*$/',
                        't.me/$1',
                        $data['link']
                    );

//                      // Проверка на существование ссылки в текущей категории
//                     $existingLink = $this->ownerModel->telegram_links()
//                         ->where('link', $link)
//                         ->exists();
//
//                     if ($existingLink) {
//                         // Если ссылка уже существует, показываем уведомление
//                         $this->notify('error', 'The link is already attached to this category.');
//                         return;
//                     }


                    $record = $this->getRelationship()->firstOrCreate(['link' => $link], [
                        'link_raw' => $data['link'],
                    ]);

                    $this->getRelationship()->syncWithoutDetaching([$record->id]);
                })
                ->color('success')
                ->icon('heroicon-o-link'),
            ])
            ->actions([
                Tables\Actions\Action::make('Edit Link')
                    ->label('Edit')
                    ->icon('heroicon-s-pencil-square')
                    ->url(fn($record): string => TelegramLinkResource::getUrl('edit', ['record' => $record->id])),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
