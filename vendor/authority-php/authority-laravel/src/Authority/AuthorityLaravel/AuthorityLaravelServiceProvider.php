<?php

namespace Authority\AuthorityLaravel;

use Authority\Authority;
use Illuminate\Support\ServiceProvider;

class AuthorityLaravelServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('authority.php'),
        ]);

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../migrations/' => base_path('/database/migrations')
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['authority'] = $this->app->share(function($app) {
            $user = $app['auth']->user();

            $authority = new Authority($user);

            $initialize = $app['config']->get('authority.initialize', null);

            if ($initialize) {
                $initialize($authority);
            }

            return $authority;
        });

        $this->app->alias('authority', 'Authority\Authority');
    }
}
