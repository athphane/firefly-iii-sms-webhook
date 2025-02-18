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
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

    public function submitTransaction(): void
    {
        $this->form->validate();

        $transaction = new Transaction();

        if (!empty($this->data['receipt'])) {
            /** @var TemporaryUploadedFile $receipt */
            $receipt = Arr::flatten($this->data['receipt'])[0];


            $path = $receipt->getPath();
            $file_name = $receipt->getFilename();
            $full_path = str($path)->append('/' . $file_name)->toString();

            if (file_exists($full_path)) {
                $response = Http::baseUrl(config('openwebui.base_url'))
                    ->acceptJson()
                    ->withToken(config('openwebui.api_key'))
                    ->attach('file', fopen($full_path, 'r'), basename($full_path))
                    ->post('v1/files');

                $data = $response->json();

                dd($data);

                $transaction->receipt = base64_encode(file_get_contents($full_path));
            }
        }

        if ($transaction->save()) {
            $transaction->process();

            Notification::make('success')
                ->title('Transaction Submitted')
                ->body('Transaction will be processed shortly.')
                ->success()
                ->send();
        }
    }
}
