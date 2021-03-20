<?php

namespace App\Repositories\Xero;

use League\OAuth2\Client\Token\AccessToken;
use App\Http\Controllers\Xero\XeroController;

use LangleyFoxall\XeroLaravel\XeroApp;

class AccountRepository
{
    private $xero;

    public function __construct(XeroController $xeroController)
    {
        $this->xero = $xeroController;
    }

    public function getAccounts()
    {
        $this->xero->refreshAccessTokenIfNecessary();

        $user = auth()->user()->getAccount();

        $xero = new XeroApp(
            new AccessToken((array)json_decode($user->xero_access_token)),
            $user->tenant_id
        );

        $accounts = $xero->accounts()
            ->where('Status', 'ACTIVE')
            ->get();

        return json_decode(json_encode($accounts));
    }
}
