<?php

namespace Tengliyun\Token;

use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TokenServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/token.php', 'token');

        $this->registerGuard();
    }

    /**
     * Register the token guard.
     *
     * @return void
     */
    protected function registerGuard(): void
    {
        Auth::resolved(function (Factory $auth) {
            $auth->extend(Token::class, function ($app, $name, array $config) use ($auth) {
                return tap($this->createGuard($auth, $name, $config), function (Guard $guard) {
                    $this->app->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Make an instance of the token guard.
     *
     * @param Factory $auth
     * @param string  $name
     * @param array   $config
     *
     * @return RequestGuard
     */
    protected function createGuard(Factory $auth, string $name, array $config): RequestGuard
    {
        return new RequestGuard(
            new TokenGuard($auth, $name, $config['provider']),
            $this->app['request'],
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'token-migrations');

            $this->publishes([
                __DIR__ . '/../config/token.php' => config_path('token.php'),
            ], 'token-config');
        }
    }

    /**
     * Register the Passport Artisan commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\KeysCommand::class,
            ]);
        }
    }
}
