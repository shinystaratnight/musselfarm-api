<?php

namespace App\Http\Controllers\Xero;

use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
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

    protected function getProvider()
    {
        return new Provider([
            'clientId'     => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri'  => $this->redirectUri,
        ]);
    }
}
