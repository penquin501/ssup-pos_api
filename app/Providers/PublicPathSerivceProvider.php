<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PublicPathSerivceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() === 'local') {
            $this->app['path.public'] = public_path();
        } else if ($this->app->environment() === 'production') {
            $this->app['path.public'] = base_path() . '/../' . env('PUBLIC_PATH');
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
