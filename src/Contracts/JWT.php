<?php

namespace Tengliyun\Token\Contracts;

use Token\JWT\Contracts\Builder;
use Token\JWT\Contracts\ClaimsFormatter;
use Token\JWT\Contracts\Constraint;
use Token\JWT\Contracts\Decoder;
use Token\JWT\Contracts\Encoder;
use Token\JWT\Contracts\Key;
use Token\JWT\Contracts\Parser;
use Token\JWT\Contracts\Signer;
use Token\JWT\Contracts\Validator;
use Token\JWT\Factory;

interface JWT
{
    public function forAsymmetricSigner(Signer $signer, Key $signingKey, Key $verificationKey, ?Encoder $encoder = null, ?Decoder $decoder = null): Factory;

    public function forSymmetricSigner(Signer $signer, Key $key, ?Encoder $encoder = null, ?Decoder $decoder = null): Factory;

    public function builder(?ClaimsFormatter $claimFormatter = null): Builder;

    public function parser(): Parser;

    public function signer(): Signer;

    public function signingKey(): Key;

    public function verificationKey(): Key;

    public function validator(): Validator;

    public function setValidator(Validator $validator): void;

    /**
     * @return Constraint[]
     */
    public function validationConstraints(): array;

    /**
     * @param Constraint[] $validationConstraints
     *
     * @return void
     */
    public function setValidationConstraints(Constraint ...$validationConstraints): void;
}
