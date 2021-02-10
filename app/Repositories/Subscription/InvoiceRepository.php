<?php

namespace App\Repositories\Subscription;

use App\Models\Payment;
use App\Services\InvoicesService;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function invoices()
    {
        $payments = Payment::where('user_id', auth()->id())->latest()->get();

        return new InvoicesService($payments);
    }
}
