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
                FileUpload::make('receipts')
                    ->label('Transaction Receipt')
                    ->required()
                    ->multiple()
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
            $receipts = $this->data['receipts'];

            /** @var TemporaryUploadedFile $receipt */
            foreach ($receipts as $key => $receipt) {
                $transaction = new Transaction();
                $transaction->save();

                $transaction->addMedia($receipt)
                    ->toMediaCollection('receipts');

                $transaction->process();
            }

            $this->data['receipt'] = null;

            Notification::make('success')
                ->title('Transactions Submitted')
                ->body('Transactions will be processed shortly.')
                ->success()
                ->send();
        });
    }
}
