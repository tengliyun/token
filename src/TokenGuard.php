<?php

namespace Tengliyun\Token;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Tengliyun\Token\Contracts\AuthToken;
use Tengliyun\Token\Contracts\HasApiToken;
use Tengliyun\Token\Events\TokenAuthenticated;

class TokenGuard
{
    /**
     * The authentication factory implementation.
     *
     * @var Factory
     */
    protected Factory $auth;

    /**
     * The guard name.
     *
     * @var string
     */
    protected string $name;

    /**
     * The provider name.
     *
     * @var string|null
     */
    protected ?string $provider = null;

    /**
     * Create a new guard instance.
     *
     * @param Factory     $auth
     * @param string      $name
     * @param string|null $provider
     *
     */
    public function __construct(Factory $auth, string $name, string $provider = null)
    {
        $this->auth     = $auth;
        $this->name     = $name;
        $this->provider = $provider;
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param Request           $request
     * @param UserProvider|null $provider
     *
     * @return HasApiToken|null
     */
    public function __invoke(Request $request, UserProvider $provider = null): ?HasApiToken
    {
        if (!$token = $this->getTokenFromRequest($request)) {
            return null;
        }

        $authTokenModel = with(Token::token(), function ($authTokenModel) use ($token) {
            return $authTokenModel::findToken($token);
        });

        if (is_null($authTokenModel)) {
            return null;
        }

        if (is_null($tokenable = $authTokenModel->tokenable)) {
            return null;
        }

        if (!$this->isValidAccessToken($authTokenModel, $tokenable) || !$this->supportsTokens($authTokenModel->tokenable)) {
            return null;
        }

        $tokenable = $authTokenModel->tokenable->withAccessToken($authTokenModel);

        event(new TokenAuthenticated($authTokenModel));

        $fill = [
            'last_used_at' => now()->toDateTimeString(),
        ];

        if (method_exists($authTokenModel->getConnection(), 'hasModifiedRecords') &&
            method_exists($authTokenModel->getConnection(), 'setRecordModificationState')) {
            tap($authTokenModel->getConnection()->hasModifiedRecords(), function ($hasModifiedRecords) use ($authTokenModel, $fill) {
                $authTokenModel->forceFill($fill)->save();
                $authTokenModel->getConnection()->setRecordModificationState($hasModifiedRecords);
            });
        } else {
            $authTokenModel->forceFill($fill)->save();
        }

        return $tokenable;
    }

    /**
     * Get the token from the request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        $token = Token::useAccessTokenRetrievalCallback($request) ?? $request->bearerToken();

        return Token::useDecryptTokens($token);
    }

    /**
     * Determine if the provided access token is valid.
     *
     * @param AuthToken   $authTokenModel
     * @param HasApiToken $tokenable
     *
     * @return bool
     */
    protected function isValidAccessToken(AuthToken $authTokenModel, HasApiToken $tokenable): bool
    {
        return Token::useAccessTokenAuthenticationCallback($authTokenModel, $this->hasValidProvider($tokenable));
    }

    /**
     * Determine if the tokenable model matches the provider's model type.
     *
     * @param HasApiToken|null $tokenable
     *
     * @return bool
     */
    protected function hasValidProvider(?HasApiToken $tokenable = null): bool
    {
        if (is_null($this->provider)) {
            return true;
        }

        if (config("auth.providers.{$this->provider}.driver") === 'database') {
            return true;
        }

        $model = config("auth.providers.{$this->provider}.model");

        return $tokenable instanceof $model;
    }

    /**
     * Determine if the tokenable model supports API tokens.
     *
     * @param HasApiToken|null $tokenable
     *
     * @return bool
     */
    protected function supportsTokens(HasApiToken $tokenable = null): bool
    {
        return $tokenable && in_array(
                HasApiTokens::class, class_uses_recursive(get_class($tokenable))
            );
    }
}
