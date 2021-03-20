<?php

namespace App\Http\Controllers\Xero;

use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

use App\Repositories\Xero\AccountRepository;
use App\Repositories\Xero\ContactRepository;

class XeroDataController extends Controller
{
    private $accountRepo;
    private $contactRepo;

    public function __construct(AccountRepository $account, ContactRepository $contact)
    {
        $this->accountRepo = $account;
        $this->contactRepo = $contact;
    }

    public function getContacts()
    {
        $contacts = $this->contactRepo->getContacts();
        $response = array_map(function ($contact) {
            return [
                'ContactID' => $contact->ContactID,
                'Name' => $contact->Name,
            ];
        }, $contacts);

        return response()->json([
            'message' => 'success',
            'data' => $response,
        ], 200);
    }

    public function getAccounts()
    {
        $accounts = $this->accountRepo->getAccounts();
        $response = array_map(function ($account) {
            return [
                'Code' => $account->Code,
                'Name' => $account->Name,
            ];
        }, $accounts);

        return response()->json([
            'message' => 'success',
            'data' => $response,
        ], 200);
    }
}
