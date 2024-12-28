<?php

namespace App\Support\FireflyIII\Providers;

use App\Support\FireflyIII\FireflyIII;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class FireflyIIIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FireflyIII::class, function () {
            $config = $this->app['config']['firefly-iii'];

            $base_url = Arr::get($config, 'base_url');
            $api_key = Arr::get($config, 'api_key');

            return new FireflyIII($base_url, $api_key);
        });

        $this->app->singleton('firefly-iii', function () {
            return $this->app->make(FireflyIII::class);
        });
    }

    public function provides(): array
    {
        return [
            FireflyIII::class,
        ];
    }


    public function boot(): void
    {
        //
    }
}
