<?php

namespace Tengliyun\Token;

use Illuminate\Contracts\Support\Arrayable;
use Tengliyun\Token\Contracts\AuthToken;
use Tengliyun\Token\Exceptions\AccessTokenException;
use Tengliyun\Token\Exceptions\RefreshTokenException;
use Tengliyun\Token\Exceptions\TokenException;
use Throwable;
use Token\JWT\Contracts\Token as JWToken;
use Token\JWT\Exceptions\ConstraintViolationException;
use Token\JWT\Exceptions\RequiredConstraintsViolated;
use Token\JWT\Factory;
use Token\JWT\Validation\Constraint\RelatedTo;
use Token\JWT\Validation\Constraint\SignedWith;
use Token\JWT\Validation\Constraint\ValidAt;

class PersonalAccessToken implements Arrayable
{
    protected ?JWToken $accessToken  = null;
    protected ?JWToken $refreshToken = null;

    public function __construct(protected Factory $factory)
    {
        //
    }

    public function accessToken(AuthToken $model): static
    {
        $this->accessToken = $this->factory->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($model->getAttribute('tokenable_type'))
            // Configures the id (jti claim)
            ->identifiedBy($model->getKey())
            // Configures the subject
            ->relatedTo('access-token')
            // Configures the audience (aud claim)
            ->permittedFor(...$model->getAttribute('scopes'))
            // Configures the time that the token was issue (iat claim)
            ->issuedAt(now()->toDateTimeImmutable())
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter(now()->toDateTimeImmutable())
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($model->getAttribute('access_token_expire_at'))
            // Builds a new token
            ->getToken($this->factory->signer(), $this->factory->signingKey());

        return $this;
    }

    public function refreshToken(AuthToken $model): static
    {
        $this->refreshToken = $this->factory->builder()
            // Configures the issuer (iss claim)
            ->issuedBy($model->getAttribute('tokenable_type'))
            // Configures the id (jti claim)
            ->identifiedBy($model->getKey())
            // Configures the subject
            ->relatedTo('refresh-token')
            // Configures the time that the token was issue (iat claim)
            ->issuedAt(now()->toDateTimeImmutable())
            // Configures the time that the token can be used (nbf claim)
            ->canOnlyBeUsedAfter($model->getAttribute('access_token_expire_at'))
            // Configures the expiration time of the token (exp claim)
            ->expiresAt($model->getAttribute('refresh_token_expire_at'))
            // Builds a new token
            ->getToken($this->factory->signer(), $this->factory->signingKey());

        return $this;
    }

    /**
     * @inheritdoc
     * @return array
     */
    #[\Override]
    public function toArray(): array
    {
        return array_filter([
            'access_token'  => $this->accessToken ? Token::useEncryptTokens($this->accessToken->toString()) : null,
            'refresh_token' => $this->refreshToken ? Token::useEncryptTokens($this->refreshToken->toString()) : null,
        ]);
    }

    /**
     * @param string $token
     *
     * @return JWToken|null
     * @throws AccessTokenException
     * @throws TokenException
     */
    public function parseAccessToken(string $token): ?JWToken
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
        } catch (ConstraintViolationException|RequiredConstraintsViolated $exception) {
            throw new AccessTokenException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $throwable) {
            throw new TokenException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        return null;
    }

    /**
     * @param string $token
     *
     * @return JWToken|null
     * @throws RefreshTokenException
     * @throws TokenException
     */
    public function parseRefreshToken(string $token): ?JWToken
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
        } catch (ConstraintViolationException|RequiredConstraintsViolated $exception) {
            throw new RefreshTokenException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $throwable) {
            throw new TokenException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        return null;
    }
}
