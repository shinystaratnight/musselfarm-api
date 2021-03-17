<?php

namespace App\Http\Controllers\Xero;

use App\Models\User;
use App\Models\Account;
use LangleyFoxall\XeroLaravel\OAuth2;
use Calcinai\OAuth2\Client\Provider\Xero as Provider;

class MyOAuth2 extends OAuth2
{
    public function __construct($userId)
    {
        $account = User::find($userId)->getAccount();
        $this->clientId = $account->client_id;
        $this->clientSecret = $account->client_secret;
        $this->redirectUri = $account->redirect_url;
        $this->scope = 'openid email profile offline_access accounting.settings.read';
    }

    public function getAuthorizationRedirect()
    {
        $provider = $this->getProvider();

        $authUri = $provider->getAuthorizationUrl(['scope' => $this->scope]);

        session()->put(self::KEYS['SESSION_STATE'], $provider->getState());

        return $authUri;
    }

    public function getAccessTokenFromXeroRequest(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (!$code) {
            throw new InvalidXeroRequestException('No `code` present in request from Xero.');
        }

        if (!$state) {
            throw new InvalidXeroRequestException('No `state` present in request from Xero.');
        }

        return $this->getProvider()->getAccessToken('authorization_code', ['code' => $code]);
    }

    protected function getProvider()
    {
        return new Provider([
            'clientId'     => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri'  => $this->redirectUri,
        ]);
    }
}
