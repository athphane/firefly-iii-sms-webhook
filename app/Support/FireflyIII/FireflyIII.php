<?php

namespace App\Support\FireflyIII;

use Illuminate\Support\Facades\Http;

class FireflyIII
{
    public string $base_url;
    public string $api_key;

    public function __construct(string $base_url, string $api_key)
    {
        $this->base_url = $base_url;
        $this->api_key = $api_key;
    }

    public function getJson(string $endpoint, array $params = [], $method = 'GET'): array
    {
        return Http::baseUrl($this->base_url)
            ->withToken($this->api_key)
            ->$method($endpoint, $params)
            ->json();
    }

    public function about(): array
    {
        return $this->getJson('/about/user');
    }
}
