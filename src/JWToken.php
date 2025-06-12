<?php

namespace Tengliyun\Token;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Tengliyun\Token\Contracts\AuthToken;
use Tengliyun\Token\Exceptions\InvalidAccessTokenException;
use Tengliyun\Token\Exceptions\InvalidRefreshTokenException;
use Tengliyun\Token\Exceptions\TokenException;
use Throwable;
use Token\JWT\Contracts\Token as JsonWebToken;
use Token\JWT\Exceptions\RequiredConstraintsViolated;
use Token\JWT\Factory;
use Token\JWT\Validation\Constraint\RelatedTo;
use Token\JWT\Validation\Constraint\SignedWith;
use Token\JWT\Validation\Constraint\ValidAt;

class JWToken implements Arrayable, Jsonable
{
    protected static ?JWToken $instance     = null;
    protected ?JsonWebToken   $accessToken  = null;
    protected ?JsonWebToken   $refreshToken = null;

    protected function __construct(protected Factory $factory)
    {
        //
    }

    public static function getInstance(): JWToken
    {
        if (is_null(static::$instance)) {
            static::$instance = new static(app('token.jwt'));
        }

        return static::$instance;
    }

    public function accessToken(AuthToken $authToken): static
    {
        $this->accessToken = $this->factory->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($authToken->getAttribute('tokenable_type'))
            // Configures the id (jti claim)
            ->identifiedBy($authToken->getKey())
            // Configures the subject
            ->relatedTo('access-token')
            // Configures the audience (aud claim)
            ->permittedFor(...$authToken->getAttribute('scopes'))
            // Configures the time that the token was issue (iat claim)
            ->issuedAt(now()->toDateTimeImmutable())
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter(now()->toDateTimeImmutable())
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($authToken->getAttribute('access_token_expire_at'))
            // Builds a new token
            ->getToken($this->factory->signer(), $this->factory->signingKey());

        return $this;
    }

    public function refreshToken(AuthToken $authToken): static
    {
        $this->refreshToken = $this->factory->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($authToken->getAttribute('tokenable_type'))
            // Configures the id (jti claim)
            ->identifiedBy($authToken->getKey())
            // Configures the subject
            ->relatedTo('refresh-token')
            // Configures the time that the token was issue (iat claim)
            ->issuedAt(now()->toDateTimeImmutable())
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($authToken->getAttribute('access_token_expire_at'))
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($authToken->getAttribute('refresh_token_expire_at'))
            // Builds a new token
            ->getToken($this->factory->signer(), $this->factory->signingKey());

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return array<string, JsonWebToken>
     */
    #[\Override]
    public function toArray(): array
    {
        return array_filter([
            'access_token'  => Token::useEncryptTokens($this->accessToken?->toString()),
            'refresh_token' => Token::useEncryptTokens($this->refreshToken?->toString()),
        ]);
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    #[\Override]
    public function toJson($options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @param string $token
     *
     * @return JsonWebToken
     * @throws InvalidAccessTokenException
     * @throws TokenException
     */
    public function parseAccessToken(string $token): JsonWebToken
    {
        try {
            $token = $this->factory->parser()->parse($token);

            $this->factory->setValidationConstraints(
                new SignedWith($this->factory->signer(), $this->factory->verificationKey()),
                new RelatedTo('access-token'),
                new ValidAt(now()->toDateTimeImmutable()),
            );
            $this->factory->validator()->assert($token, ...$this->factory->validationConstraints());

            return $token;
        } catch (RequiredConstraintsViolated $exception) {
            throw new InvalidAccessTokenException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $throwable) {
            throw new TokenException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param string $token
     *
     * @return JsonWebToken
     * @throws InvalidRefreshTokenException
     * @throws TokenException
     */
    public function parseRefreshToken(string $token): JsonWebToken
    {
        try {
            $token = $this->factory->parser()->parse($token);

            $this->factory->setValidationConstraints(
                new SignedWith($this->factory->signer(), $this->factory->verificationKey()),
                new RelatedTo('refresh-token'),
                new ValidAt(now()->toDateTimeImmutable()),
            );
            $this->factory->validator()->assert($token, ...$this->factory->validationConstraints());

            return $token;
        } catch (RequiredConstraintsViolated $exception) {
            throw new InvalidRefreshTokenException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $throwable) {
            throw new TokenException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
