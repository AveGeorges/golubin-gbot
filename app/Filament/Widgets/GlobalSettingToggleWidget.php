<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Toggle;
use App\Models\GlobalSetting;

class GlobalSettingToggleWidget extends Widget implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public $categoryLinksFeature;

    protected static string $view = 'filament.widgets.global-setting-toggle-widget';

    public function mount(): void
    {
        $this->form->fill([
            'categoryLinksFeature' => GlobalSetting::where('key', 'category_links_feature')->value('value') === 'on',
        ]);
    }

    public function submit(): void
    {
        $newValue = $this->categoryLinksFeature ? 'on' : 'off';
        GlobalSetting::updateOrCreate(
            ['key' => 'category_links_feature'],
            ['value' => $newValue]
        );
    }

    protected function getFormSchema(): array
    {
        return [
            Toggle::make('categoryLinksFeature')
                ->label(__('Category Links Feature'))
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->submit()),
        ];
    }
}
