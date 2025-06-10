<?php

namespace Tengliyun\Token;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Tengliyun\Token\Contracts\AuthToken;
use Tengliyun\Token\Models\AuthTokens;

class Token
{
    /**
     * The storage location of the encryption keys.
     *
     * @var string|null
     */
    protected static ?string $keyPath = null;

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
     * A callback that can add to the validation of the access token.
     *
     * @var callable|null
     */
    public static $accessTokenAuthenticationCallback;

    /**
     * The callback used to encrypt JWT tokens.
     *
     * @var callable|null
     */
    protected static $tokenEncryptionCallback = null;

    /**
     * The callback used to decrypt JWT tokens.
     *
     * @var callable|null
     */
    protected static $tokenDecryptionCallback;

    /**
     * The auth token model class name.
     *
     * @var string
     */
    public static string $authTokenModel = AuthTokens::class;

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
     * @return static
     */
    public static function accessTokenRetrievalCallbackUsing(callable $callback): static
    {
        static::$accessTokenRetrievalCallback = $callback;

        return new static;
    }

    /**
     * Specify a callback that should be used to fetch the refresh token from the request.
     *
     * @param callable $callback
     *
     * @return static
     */
    public static function refreshTokenRetrievalCallbackUsing(callable $callback): static
    {
        static::$refreshTokenRetrievalCallback = $callback;

        return new static;
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

    public static function accessTokenAuthenticationCallback(): ?callable
    {
        return static::$accessTokenAuthenticationCallback;
    }

    /**
     * A callback that can add to the validation of the access token.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function accessTokenAuthenticationCallbackUsing(callable $callback): static
    {
        static::$accessTokenAuthenticationCallback = $callback;

        return new static;
    }

    public static function useAccessTokenAuthenticationCallback(AuthToken $authToken, bool $isValid): bool
    {
        if (is_callable(static::$accessTokenAuthenticationCallback)) {
            return call_user_func(static::$accessTokenAuthenticationCallback, $authToken, $isValid);
        }

        return $isValid;
    }

    /**
     * Specify a callback that will be used to encrypt JWT tokens.
     *
     * @param callable $callback
     *
     * @return static
     */
    public static function encryptTokensUsing(callable $callback): static
    {
        static::$tokenEncryptionCallback = $callback;

        return new static;
    }

    /**
     * Specify a callback that will be used to decrypt JWT tokens before verification.
     *
     * @param callable $callback
     *
     * @return static
     */
    public static function decryptTokensUsing(callable $callback): static
    {
        static::$tokenDecryptionCallback = $callback;

        return new static;
    }

    /**
     * Apply the user-defined encryption callback to the given JWT string.
     *
     * @description
     * If an encryption callback has been registered via encryptTokensUsing(), it will be applied
     * to the provided JWT string. Otherwise, the original JWT string will be returned unmodified.
     *
     * @param string $jwt The raw JWT string to encrypt.
     *
     * @return string The encrypted JWT string or the original string if no callback is defined.
     */
    public static function useEncryptTokens(string $jwt): string
    {
        if (is_callable(static::$tokenEncryptionCallback)) {
            return call_user_func(static::$tokenEncryptionCallback, $jwt);
        }

        return $jwt;
    }

    /**
     * Apply the user-defined decryption callback to the given token string.
     *
     * @description
     * If a decryption callback has been registered via decryptTokensUsing(), it will be used
     * to convert the encrypted token back to its original JWT form. If no callback is defined,
     * the original token string is returned.
     *
     * @param string|null $jwt The encrypted JWT string to decrypt.
     *
     * @return string|null The decrypted raw JWT string or the original string if no callback is defined.
     */
    public static function useDecryptTokens(string $jwt = null): ?string
    {
        if (is_callable(static::$tokenDecryptionCallback)) {
            return call_user_func(static::$tokenDecryptionCallback, $jwt);
        }

        return $jwt;
    }

    /**
     * Set the auth token model class name.
     *
     * @param string $authTokenModel
     *
     * @return static
     */
    public static function tokenModelUsing(string $authTokenModel): static
    {
        static::$authTokenModel = $authTokenModel;

        return new static;
    }

    /**
     * Get the auth token model class name.
     *
     * @return string
     */
    public static function tokenModel(): string
    {
        return static::$authTokenModel;
    }

    /**
     * Get a auth token model instance.
     *
     * @return AuthToken
     */
    public static function token(): AuthToken
    {
        return new static::$authTokenModel;
    }
}
