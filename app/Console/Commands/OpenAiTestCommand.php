<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OpenAiTestCommand extends Command
{
    protected $signature = 'test:openai';

    protected $description = 'Command description';

    public function handle(): void
    {
        OpenAI::setBaseUrl('');
        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $this->line($result->choices[0]->message->content);
    }
}
