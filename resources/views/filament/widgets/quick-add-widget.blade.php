<x-filament-widgets::widget>
    <x-filament::section>
        {{ $this->form }}

        <x-filament::button wire:click="submitTransaction" class="mt-3">
            Submit
        </x-filament::button>
    </x-filament::section>
</x-filament-widgets::widget>
