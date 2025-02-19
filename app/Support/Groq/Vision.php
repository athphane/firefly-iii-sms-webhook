<?php

namespace App\Support\Groq;

use LucianoTonet\GroqPHP\Groq;
use LucianoTonet\GroqPHP\GroqException;

class Vision extends \LucianoTonet\GroqPHP\Vision
{
    public Groq $groq;
    public string $defaultModel = 'llama-3.2-90b-vision-preview';

    public function __construct(Groq $groq)
    {
        parent::__construct($groq);
        $this->groq = $groq;
    }

    /**
     * Analyzes an image and returns the model's response.
     *
     * @param  string  $imagePathOrUrl  Path or URL of the image.
     * @param  string  $prompt          Question or context for the analysis.
     * @param  array   $options         Additional options for the analysis.
     * @return array Model's response.
     * @throws GroqException
     */
    public function analyze(string $imagePathOrUrl, string $prompt, array $options = []): array
    {
        $imageContent = $this->getImageContent($imagePathOrUrl);

        $messages = [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    [
                        'type' => 'image_url',
                        'image_url' => ['url' => $imageContent],
                    ],
                ],
            ],
        ];

        $model = $options['model'] ?? $this->defaultModel;

        $requestOptions = [
            'model' => $model,
            'messages' => $messages,
        ];

        if (isset($options['temperature'])) {
            $requestOptions['temperature'] = $options['temperature'];
        }
        if (isset($options['max_tokens'])) {
            $requestOptions['max_tokens'] = $options['max_tokens'];
        }
        if (isset($options['response_format'])) {
            $requestOptions['response_format'] = $options['response_format'];
        }

        return $this->groq->chat()->completions()->create($requestOptions);
    }

    /**
     * @throws GroqException
     */
    private function getImageContent(string $imagePathOrUrl): string
    {
        if (filter_var($imagePathOrUrl, FILTER_VALIDATE_URL)) {
            $headers = get_headers($imagePathOrUrl, 1);
            $fileSize = isset($headers['Content-Length']) ? (int)$headers['Content-Length'] : 0;
            if ($fileSize > 20 * 1024 * 1024) {
                throw new GroqException(
                    "Image URL exceeds 20MB size limit",
                    400,
                    'ImageSizeLimitExceededException'
                );
            }
            return $imagePathOrUrl;
        }

        if (file_exists($imagePathOrUrl)) {
            $fileSize = filesize($imagePathOrUrl);
            if ($fileSize > 4 * 1024 * 1024) {
                throw new GroqException(
                    "Local image file exceeds 4MB size limit for base64 encoding",
                    400,
                    'ImageSizeLimitExceededException'
                );
            }
            $imageData = base64_encode(file_get_contents($imagePathOrUrl));
            $mimeType = mime_content_type($imagePathOrUrl);
            return "data:$mimeType;base64," . $imageData;
        }

        throw new GroqException(
            "Image file not found: $imagePathOrUrl",
            404,
            'FileNotFoundException'
        );
    }
}
