<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Arr;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ImageQuickAddWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.image-quick-add-widget';
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
                FileUpload::make('receipt')
                    ->label('Transaction Receipt')
                    ->required()
            ]);
    }

    /**
     * @return void
     * @throws FileDoesNotExist|FileIsTooBig
     */
    public function submitTransaction(): void
    {
        $this->form->validate();

        DB::transaction(function () {
            $transaction = new Transaction();
            $transaction->save();

            if (!empty($this->data['receipt'])) {
                /** @var TemporaryUploadedFile $receipt */
                $receipt = Arr::flatten($this->data['receipt'])[0];

                $transaction->addMedia($receipt)
                    ->toMediaCollection('receipt');

                $this->data['receipt'] = null;
            }

            $transaction->process();
            Notification::make('success')
                ->title('Transaction Submitted')
                ->body('Transaction will be processed shortly.')
                ->success()
                ->send();
        });
    }
}
