<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use App\Models\NegativeKeyword;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;

class BulkAddNegativeKeywordsWidget extends Widget implements HasForms
{

    use InteractsWithForms;

    public ?Category $record = null;

    protected static string $view = 'filament.resources.category-resource.widgets.bulk-add-keywords-widget';

    protected int|string|array $columnSpan = 'full';


    public ?array $data = [];

    public function mount(): void
    {
//        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('keywords')
                    ->label('Добавить минус ключевые слова')
                    ->placeholder('ключевое слово1|ключевое слово2|ключевое слово3')
                    ->required(),
            ])
            ->statePath('data');
    }


    public function create(): void
    {
        $keywords = explode('|', $this->data['keywords']);
        $records = [];
        foreach ($keywords as $keyword) {
            $records[] = [
                'keyword' => $keyword,
                'category_id' => $this->record->id,
            ];
        }
        NegativeKeyword::insert($records);
        $this->form->fill();

    }

}
