<?php

namespace App\Http\Controllers\Xero;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use League\OAuth2\Client\Token\AccessToken;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Xero\XeroConnectRequest;

use LangleyFoxall\XeroLaravel\XeroApp;

class XeroController extends Controller
{
    private function getOAuth2($user)
    {
        // This will use the 'default' app configuration found in your 'config/xero-laravel-lf.php` file.
        // If you wish to use an alternative app configuration you can specify its key (e.g. `new OAuth2('other_app')`).
        return new MyOAuth2($user);
    }

    public function redirectUserToXero(XeroConnectRequest $request)
    {
        // Step 1 - Redirect the user to the Xero authorization URL.
        $attr = $request->validated();
        $account = User::find($attr['user_id'])->getAccount();
        $account->client_id = $attr['client_id'];
        $account->client_secret = $attr['client_secret'];
        $account->redirect_url = $attr['redirect_url'];
        $account->save();
        return $this->getOAuth2($attr['user_id'])->getAuthorizationRedirect();
    }

    public function handleAuthCallbackFromXero(Request $request, $token)
    {
        $userId = base64_decode($token);
        // Step 2 - Capture the response from Xero, and obtain an access token.
        $accessToken = $this->getOAuth2($userId)->getAccessTokenFromXeroRequest($request);

        // Step 3 - Retrieve the list of tenants (typically Xero organisations), and let the user select one.
        $tenants = $this->getOAuth2($userId)->getTenants($accessToken);
        $selectedTenant = $tenants[0]; // For example purposes, we're pretending the user selected the first tenant.

        // Step 4 - Store the access token and selected tenant ID against the user's account for future use.
        // You can store these anyway you wish. For this example, we're storing them in the database using Eloquent.
        $account = User::find($userId)->getAccount();
        $account->xero_access_token = json_encode($accessToken);
        $account->tenant_id = $selectedTenant->tenantId;
        $account->save();

        return redirect(config('services.api.front_end_url') . '/loading/xero');
    }

    public function refreshAccessTokenIfNecessary()
    {
        // Step 5 - Before using the access token, check if it has expired and refresh it if necessary.
        $account = auth()->user()->getAccount();
        $accessToken = new AccessToken((array)json_decode($account->xero_access_token));

        if ($accessToken->hasExpired()) {
            $accessToken = $this->getOAuth2(auth()->user()->id)->refreshAccessToken($accessToken);

            $account->xero_access_token = json_encode((array)$accessToken);
            $account->save();
        }
    }

    // Xero test function
    // public function getSomeData()
    // {
    //     $user = auth()->user()->getAccount(); 

    //     $this->refreshAccessTokenIfNecessary();

    //     $xero = new XeroApp(
    //         new AccessToken((array)json_decode($user->xero_access_token)),
    //         $user->tenant_id
    //     );

    //     $contacts = $xero->accounts;
    //     return response([
    //         'data' => $contacts
    //     ], 200);        
    // }
}
