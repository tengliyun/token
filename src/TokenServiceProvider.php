<?php

namespace Tengliyun\Token;

use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Tengliyun\Token\Contracts\JWT;
use Token\JWT\Contracts\Signer;
use Token\JWT\Factory as JWTFactory;
use Token\JWT\Key;
use Token\JWT\Signature\Hmac;
use Token\JWT\Signature\OpenSSL;

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

        $this->app->when(PersonalAccessToken::class)
            ->needs(JWTFactory::class)
            ->give(fn() => app(JWT::class));

        $this->registerJonsWebToken();
        $this->registerGuard();
    }

    /**
     * Register JWT (JSON Web Token) services into the Laravel service container.
     *
     * @return void
     */
    protected function registerJonsWebToken(): void
    {
        $this->app->singleton(JWT::class, function (): JWTFactory {
            $signer = config('token.signer');
            $signer = $this->app->make($signer);

            return match (true) {
                is_subclass_of($signer, OpenSSL::class) => $this->forAsymmetricSigner($signer),
                is_subclass_of($signer, Hmac::class) => $this->forSymmetricSigner($signer),
                default => JWTFactory::forUnsecuredSigner()
            };
        });
    }

    private function forAsymmetricSigner(Signer $signer): JWTFactory
    {
        return JWTFactory::forAsymmetricSigner(
            $signer,
            Key::file(Token::keyPath(config('token.private_key'))),
            Key::file(Token::keyPath(config('token.public_key')))
        );
    }

    private function forSymmetricSigner(Signer $signer): JWTFactory
    {
        return JWTFactory::forSymmetricSigner(
            $signer,
            Key::plainText(config('token.secret_key') ?? '')
        );
    }

    /**
     * Register the token guard.
     *
     * @return void
     */
    protected function registerGuard(): void
    {
        Auth::resolved(function (AuthFactory $auth) {
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
     * @param AuthFactory $auth
     * @param string      $name
     * @param array       $config
     *
     * @return RequestGuard
     */
    protected function createGuard(AuthFactory $auth, string $name, array $config): RequestGuard
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
