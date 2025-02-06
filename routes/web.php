<?php

// use Illuminate\Support\Facades\Route;

// Route::get('/gemini', function() {
//     $result = \Gemini\Laravel\Facades\Gemini::generativeModel(\Gemini\Enums\ModelType::EMBEDDING)
//         ->generateContent('Hello');
//
//     return $result->text();
// });


use App\Http\Controllers\TelegramWebhookController;

Route::match(['GET', 'POST'], '/{telegram_token}/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');
