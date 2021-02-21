<?php

namespace App\Http\Controllers\Subscription;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Services\InvoicesService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
//    public function getInvoices()
//    {
//        $payments = Payment::where('user_id', auth()->id())->latest()->get();
//
//        return new InvoicesService($payments);
//    }

    public function downloadInvoice($paymentId)
    {
        // $payment = Payment::where('user_id', auth()->id())->where('id', $paymentId)->firstOrFail();

        // $filename = storage_path('app/invoices/' . $payment->id . '.pdf');

        // if(!file_exists($filename)) {
        //     return response()->json(['status' => 'Error', 'message' => 'Current file does not exist'], 404);
        // }

        // return response()->download($filename);
        return auth()->user()->downloadInvoice($paymentId, [
            'vendor' => 'Mussel Farm',
            'product' => 'Basic Plan',
        ]);
    }
}
