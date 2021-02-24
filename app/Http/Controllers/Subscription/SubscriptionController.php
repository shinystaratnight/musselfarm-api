<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\SubscriptionRequest;
use App\Http\Requests\Subscription\SubscriptionUpdateRequest;
use App\Http\Requests\Subscription\CardUpdateRequest;
use App\Http\Requests\Subscription\TrialUpdateRequest;
use App\Repositories\Subscription\PlanRepositoryInterface as Plan;
use App\Repositories\Subscription\ResumeSubscriptionRepositoryInterface as Resume;
use App\Repositories\Subscription\SubscriptionRepositoryInterface as Subscription;
use App\Repositories\Subscription\CancelSubscriptionRepositoryInterface as Cancel;
use App\Repositories\Subscription\InvoiceRepositoryInterface as Invoice;

class SubscriptionController extends Controller
{
    private $subRepo;
    private $cancelRepo;
    private $resumeRepo;
    private $invoiceRepo;
    private $planRepo;

    public function __construct(Subscription $subscription,
                                Cancel $cancel,
                                Resume $resume,
                                Plan $plan,
                                Invoice $invoice)
    {
        $this->subRepo = $subscription;
        $this->cancelRepo = $cancel;
        $this->resumeRepo = $resume;
        $this->invoiceRepo = $invoice;
        $this->planRepo = $plan;
    }

    public function index()
    {
        $plans = $this->planRepo->getAllPlans();

        $invoices = $this->invoiceRepo->invoices();

        $data['plans'] = $plans;

        $data['invoices'] = $invoices;

        return $data;
    }

    public function getSubscriptionStatus() // get current user's subscription status
    {
        return $this->subRepo->getSubscriptionStatus();
    }

    public function getSubscription(SubscriptionRequest $request) // subscribing user to some plan
    {
        $attr = $request->validated();

        return $this->subRepo->subscription($attr);
    }

    public function updateSubscription(SubscriptionUpdateRequest $request)
    {
        $attr = $request->validated();

        return $this->subRepo->updateSubscription($attr);
    }

    public function updateTrial(TrialUpdateRequest $request)
    {
        $attr = $request->validated();

        return $this->subRepo->updateTrial($attr);
    }

    public function updateCard(CardUpdateRequest $request)
    {
        $attr = $request->validated();

        return $this->subRepo->updateCard($attr);
    }

    public function deleteCard()
    {
        return $this->subRepo->deleteCard();
    }

    public function cancelSubscription() // cancel current subscription
    {
        return $this->cancelRepo->cancel();
    }

    public function getResume() // resuming cancelled subscription
    {
        return $this->resumeRepo->resume();
    }
}




//        dd($user->stripe_id);
////        dd(auth()->user()->createAsStripeCustomer());
//
//        $stripe = new StripeClient(config('services.stripe.stripe_secret'));
//
//        $paymentMethod = $stripe->paymentMethods->create([
//            'type' => 'card',
//            'card' => [
//                'number' => '4242424242424242',
//                'exp_month' => 11,
//                'exp_year' => 2021,
//                'cvc' => '314',
//            ]
//        ])->attach(['customer' => 'cus_INRqPXieKmgfSx']);
//        dd($ss->id);
//        $rr = $ss->attach(['customer' => 'cus_INRqPXieKmgfSx']);
//        dd($rr);
//        $d = auth()->user()->newSubscription('Basic plan', 'price_1HlJODL4uVih9mlIztPAd4KW')
//                           ->trialDays(config('services.stripe.stripe_trial'))
//                           ->create($paymentMethod->id);
//        dd($d);
//        pm_1Hn5VZL4uVih9mlITPuuZLsN
//    }
//}


//        $paymentMethods = auth()->user()->paymentMethods()->map(function($paymentMethod)
//        {
//            return $paymentMethod->asStripePaymentMethod();
//        });
//
//        dd($paymentMethods);
////
//        $d = auth()->user()->newSubscription('Basic plan', 'price_1HlJODL4uVih9mlIztPAd4KW')
//            ->create($paymentMethods[0]['id']);

/////
//update cardholder info

//$stripe = new \Stripe\StripeClient(
//    'sk_test_51HQrzjL4uVih9mlILwiESxLjFRCEUkgP7pC99qk2owB3xetX8phRhleijk8Qkp6wxhxMcyeQolLIxeiRRh8Mv9vd00LCQ9WsVB'
//);
//$stripe->issuing->cardholders->create([
//    'type' => 'individual',
//    'name' => 'Jenny Rosen',
//    'email' => 'jenny.rosen@example.com',
//    'phone_number' => '+18888675309',
//    'billing' => [
//        'address' => [
//            'line1' => '1234 Main Street',
//            'city' => 'San Francisco',
//            'state' => 'CA',
//            'country' => 'US',
//            'postal_code' => '94111',
//        ],
//    ],
//]);

///
