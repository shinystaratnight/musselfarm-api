<?php

namespace App\Repositories\Xero;

use League\OAuth2\Client\Token\AccessToken;
use App\Http\Controllers\Xero\XeroController;

use XeroPHP\Application;
use XeroPHP\Models\Accounting\Account;
use XeroPHP\Models\Accounting\Contact;
use XeroPHP\Models\Accounting\Invoice;
use XeroPHP\Models\Accounting\LineItem;

use LangleyFoxall\XeroLaravel\XeroApp;

class InvoiceRepository
{
    private $xero;

    public function __construct(XeroController $xeroController)
    {
        $this->xero = $xeroController;
    }

    public function addInvoice($expense)
    {
        $this->xero->refreshAccessTokenIfNecessary();

        $user = auth()->user()->getAccount(); 

        $token = json_decode($user->xero_access_token);
        $xero = new Application($token->access_token, $user->tenant_id);

        $contacts = $xero->load(Contact::class)
            ->where('ContactID', $expense['from'])
            ->execute();

        $invoice = new Invoice($xero);

        $invoice->setType('ACCPAY');
        $invoice->setContact($contacts[0]);
        $invoice->setDate(new \DateTime('@'.substr($expense['date'], 0, 10)));
        $invoice->setDueDate(new \DateTime('@'.substr($expense['due_date'], 0, 10)));

        $lineItem = new LineItem($xero);
        $lineItem->setDescription($expense['expenses_name']);
        $lineItem->setQuantity(1);
        $lineItem->setUnitAmount($expense['price_actual']);
        $lineItem->setAccountCode($expense['account']);

        $invoice->addLineItem($lineItem);
        $invoice->save();
    }

    public function updateInvoice($budget, $expense)
    {
        $this->xero->refreshAccessTokenIfNecessary();

        $user = auth()->user()->getAccount(); 
        $token = json_decode($user->xero_access_token);
        $xero = new Application($token->access_token, $user->tenant_id);

        $oldData = $budget['rdata'] ? json_decode($budget['rdata']) : null;
        
        $invoiceId = '';
        if ($oldData) {
            $idate = new \DateTime('@'.substr($oldData->date, 0, 10));
            $iduedate = new \DateTime('@'.substr($oldData->due_date, 0, 10));
            $dateString = date($idate->format('Y, m, d'));
            $dueDateString = date($iduedate->format('Y, m, d'));

            $invoices = $xero->load(Invoice::class)
                ->where('Type', 'ACCPAY')
                ->where(sprintf('Date >= DateTime(%s) && Date <= DateTime(%s)', $dateString, $dateString))
                ->where(sprintf('DueDate >= DateTime(%s) && DueDate <= DateTime(%s)', $dueDateString, $dueDateString))
                ->where('Contact.ContactID', $oldData->from)
                ->where('SubTotal', floatval($oldData->price_actual))
                ->execute();

            foreach ($invoices as $invoice) {
                $invoiceDetail = $xero->loadByGUID(Invoice::class, $invoice->InvoiceID);
                if ($invoiceDetail['LineItems'][0]['Description'] == $budget['expenses_name']) {
                    $invoiceId = $invoice->InvoiceID;
                    break;
                }
            }
        }
        
        $invoice = new Invoice($xero);

        $contacts = $xero->load(Contact::class)
            ->where('ContactID', $expense['from'])
            ->execute();

        $invoice->setType('ACCPAY');
        $invoice->setContact($contacts[0]);
        $invoice->setDate(new \DateTime('@'.substr($expense['date'], 0, 10)));
        $invoice->setDueDate(new \DateTime('@'.substr($expense['due_date'], 0, 10)));  
        if ($invoiceId) {
            $invoice->setInvoiceID($invoiceId);
        }

        $lineItem = new LineItem($xero);
        $lineItem->setDescription($budget['expenses_name']);
        $lineItem->setQuantity(1);
        $lineItem->setUnitAmount($expense['value']);
        $lineItem->setAccountCode($expense['account']);

        $invoice->addLineItem($lineItem);
        $invoice->save();
    }
}
