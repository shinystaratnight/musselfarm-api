<?php
namespace App;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CAuthorizationServer extends AuthorizationServer
{
    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseInterface $response)
    {
        $remember = request()->input('remember');
        foreach ($this->enabledGrantTypes as $grantType) {
            if (!$grantType->canRespondToAccessTokenRequest($request)) {
                continue;
            }

            $tokenResponse = $grantType->respondToAccessTokenRequest(
                $request,
                $this->getResponseType(),
                $remember
                    ? new DateInterval('P1Y')
                    : $this->grantTypeAccessTokenTTL[$grantType->getIdentifier()]
            );

            if ($tokenResponse instanceof ResponseTypeInterface) {
                return $tokenResponse->generateHttpResponse($response);
            }
        }

        throw OAuthServerException::unsupportedGrantType();
    }
}
