<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickAddWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.quick-add-widget';
    protected int|string|array $columnSpan = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Textarea::make('message')
                    ->label('Transaction Message')
                    ->required()
                    ->string()
            ]);
    }

    public function submitTransaction(): void
    {
        $this->form->validate();

        $transaction = new Transaction(['message' => $this->data['message']]);

        if ($transaction->save()) {
            $transaction->process();

            Notification::make('success')
                ->title('Transaction Submitted')
                ->body('Transaction will be processed shortly.')
                ->success()
                ->send();
        }

        $this->data['message'] = null;
    }
}
