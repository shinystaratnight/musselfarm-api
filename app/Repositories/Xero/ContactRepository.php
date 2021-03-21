<?php

namespace App\Repositories\Xero;

use League\OAuth2\Client\Token\AccessToken;
use App\Http\Controllers\Xero\XeroController;

use LangleyFoxall\XeroLaravel\XeroApp;

class ContactRepository
{
    private $xero;

    public function __construct(XeroController $xeroController)
    {
        $this->xero = $xeroController;
    }

    public function getContacts()
    {
        $usr = auth()->user()->getAccount();
        if ($usr->xero_access_token) {
            $this->xero->refreshAccessTokenIfNecessary();

            $user = auth()->user()->getAccount(); 

            $xero = new XeroApp(
                new AccessToken((array)json_decode($user->xero_access_token)),
                $user->tenant_id
            );

            $contacts = $xero->contacts()
                ->where('ContactStatus', 'ACTIVE')
                ->get();

            return json_decode(json_encode($contacts));
        }
        return [];
    }
}
