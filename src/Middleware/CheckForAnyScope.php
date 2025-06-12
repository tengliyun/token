<?php

namespace Tengliyun\Token\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tengliyun\Token\Exceptions\InvalidAccessTokenException;
use Tengliyun\Token\Exceptions\MissingScopeException;

class CheckForAnyScope
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed   ...$scopes
     *
     * @return Response
     * @throws InvalidAccessTokenException
     * @throws MissingScopeException
     */
    public function handle(Request $request, Closure $next, ...$scopes): Response
    {
        if (!$request->user()?->authToken()) {
            throw new InvalidAccessTokenException();
        }

        foreach ($scopes as $scope) {
            if ($request->user()->tokenCan($scope)) {
                return $next($request);
            }
        }

        throw new MissingScopeException($scopes);
    }
}
