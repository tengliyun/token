<?php

namespace Tengliyun\Token;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;

class Token
{
    /**
     * The interval when access tokens expire.
     */
    protected static ?DateInterval $accessTokensExpireIn = null;

    /**
     * The date when refresh tokens expire.
     */
    protected static ?DateInterval $refreshTokensExpireIn = null;

    /**
     * A callback that can get the access tokens from the request.
     *
     * @var callable|null
     */
    protected static $accessTokenRetrievalCallback = null;

    /**
     * A callback that can get the refresh tokens from the request.
     *
     * @var callable|null
     */
    protected static $refreshTokenRetrievalCallback = null;

    /**
     * The storage location of the encryption keys.
     *
     * @var string|null
     */
    protected static ?string $keyPath = null;

    /**
     * Get or set when access tokens expire.
     *
     * @param DateTimeInterface|DateInterval|null $date
     *
     * @return DateInterval|static
     */
    public static function accessTokensExpireIn(DateTimeInterface|DateInterval|null $date = null): DateInterval|static
    {
        if (is_null($date)) {
            return static::$accessTokensExpireIn ?? new DateInterval('P1Y');
        }

        static::$accessTokensExpireIn = $date instanceof DateTimeInterface
            ? Carbon::now()->diff($date)
            : $date;

        return new static;
    }

    /**
     * Get or set when refresh tokens expire.
     *
     * @param DateTimeInterface|DateInterval|null $date
     *
     * @return DateInterval|static
     */
    public static function refreshTokensExpireIn(DateTimeInterface|DateInterval|null $date = null): DateInterval|static
    {
        if (is_null($date)) {
            return static::$refreshTokensExpireIn ?? new DateInterval('P1Y');
        }

        static::$refreshTokensExpireIn = $date instanceof DateTimeInterface
            ? Carbon::now()->diff($date)
            : $date;

        return new static;
    }

    /**
     * Get the callback that should be used to fetch the access token.
     *
     * @return callable|null
     */
    public static function accessTokenRetrievalCallback(): ?callable
    {
        return static::$accessTokenRetrievalCallback;
    }

    /**
     * Get the callback that should be used to fetch the refresh token.
     *
     * @return callable|null
     */
    public static function refreshTokenRetrievalCallback(): ?callable
    {
        return static::$refreshTokenRetrievalCallback;
    }

    /**
     * Specify a callback that should be used to fetch the access token from the request.
     *
     * @param callable $callback
     *
     * @return void
     */
    public static function accessTokenRetrievalCallbackUsing(callable $callback): void
    {
        static::$accessTokenRetrievalCallback = $callback;
    }

    /**
     * Specify a callback that should be used to fetch the refresh token from the request.
     *
     * @param callable $callback
     *
     * @return void
     */
    public static function refreshTokenRetrievalCallbackUsing(callable $callback): void
    {
        static::$refreshTokenRetrievalCallback = $callback;
    }

    /**
     * Get the access token from the request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public static function useAccessTokenRetrievalCallback(Request $request): ?string
    {
        if (is_callable(static::$accessTokenRetrievalCallback)) {
            return call_user_func(static::$accessTokenRetrievalCallback, $request);
        }

        return null;
    }

    /**
     * Get the refresh token from the request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public static function useRefreshTokenRetrievalCallback(Request $request): ?string
    {
        if (is_callable(static::$refreshTokenRetrievalCallback)) {
            return call_user_func(static::$refreshTokenRetrievalCallback, $request);
        }

        return null;
    }

    /**
     * Set the storage location of the encryption keys.
     *
     * @param string $path
     *
     * @return void
     */
    public static function loadKeysFrom(string $path): void
    {
        static::$keyPath = ltrim($path, '/\\');
    }

    /**
     * The location of the encryption keys.
     *
     * @param string $file
     *
     * @return string
     */
    public static function keyPath(string $file): string
    {
        $file = ltrim($file, '/\\');

        return static::$keyPath ? static::$keyPath . DIRECTORY_SEPARATOR . $file : storage_path($file);
    }
}
