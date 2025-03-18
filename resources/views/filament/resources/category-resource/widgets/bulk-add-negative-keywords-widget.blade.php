<x-filament-widgets::widget>
    <x-filament::section>
        <form wire:submit="create">
            {{ $this->form }}

            <x-filament::button type="submit" class="mt-3">
                Add
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
