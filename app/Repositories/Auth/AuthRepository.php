<?php

namespace App\Repositories\Auth;

use App\Models\FarmUtil;
use App\Models\Payment;
use App\Models\Plan;
use App\Notifications\ChargeSuccessNotification;
use App\Notifications\NewSubscriptionNotification;
use Stripe\StripeClient;
use App\Models\Invite;
use App\Models\Inviting;
use App\Models\User;
use App\Models\Account;
use App\Models\UserProfile;
use App\Services\InvoicesService;
use App\Notifications\SignupActivate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\ApiResponser;
use GuzzleHttp\Client;
use Laravel\Passport\Client as OClient;


class AuthRepository implements AuthRepositoryInterface
{
    use ApiResponser;

    public function registerUser($attr)
    {
        $user = User::create([
            'email' => $attr['email'],
            'password' => Hash::make($attr['password']),
            'activation_token' => Str::random(60),
            'coupon' => $attr['coupon'],
            'quantity' => 1,
        ]);

        if($user) {

            UserProfile::create([
                'user_id' => $user->id,
                'name' => $attr['name']
            ]);

            $user->assignRole('owner');
            $user->givePermissionTo('view');
            $user->givePermissionTo('edit');
            $user->givePermissionTo('finance');
        }

        $user->notify(new SignupActivate($user));

        return response(['status' => 'Success', 'email' => $attr['email']], 201);
    }

    public function login($attr)
    {
        $attr['active'] = 1;
        $attr['deleted_at'] = null;

        if (!Auth::attempt(['email' => $attr['email'], 'password' => $attr['password']])) {
            return $this->error('Credentials mismatch', 401);
        }

        if(Auth::attempt(['email' => $attr['email'], 'password' => $attr['password'], 'active' => 1])) {
            $oClient = OClient::where('password_client', 1)->first();
            return $this->getTokens($oClient, $attr['email'], $attr['password']);

        } else {

            return response(['message' => 'Account is not active'], 401);

        }
    }

    public function resend($attr)
    {
        $users = User::where(['email' => $attr['email'], 'active' => 0])->get();

        if($users) {
            foreach ($users as $user) {
                $user->notify(new SignupActivate($user));
            }
        }

        return response()->json(['message' => 'Success'], 200);
    }

    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return redirect( config('services.api.front_end_url'));
        }

        $user->active = true;

        $user->activation_token = '';

        $trialDays = config('services.stripe.stripe_trial');
        if ($user->coupon == 'coupon1') $trialDays = 30;
        else if ($user->coupon == 'coupon2') $trialDays = 60;
        $user->trial_ends_at = now()->addDays($trialDays);

        $user->quantity = 1;

        $account = Account::create([]);
        $user->account_id = $account->id;
        $user->save();

        // Set Default Farms Util
        $defaultFarmsUtilData = [
            ['name' => 'D-Seed1', 'type' =>'seed'],
            ['name' => 'D-Seed2', 'type' =>'seed'],
            ['name' => 'D-Seed3', 'type' =>'seed'],
            ['name' => 'D-Maintenance1', 'type' =>'maintenance'],
            ['name' => 'D-Maintenance2', 'type' =>'maintenance'],
            ['name' => 'D-Maintenance3', 'type' =>'maintenance'],
        ];
        $farmUtils = array_map(function ($util) {
            $util['user_id'] = $user->id;
            return $util;
        }, $defaultFarmsUtilData);

        FarmUtil::insert($farmUtils);

        return redirect( config('services.api.front_end_url') . '/sign-in/checked');
    }

    public function getUserRolePermissions($attr)
    {
        dd(auth()->user()->roles);
        return auth()->user()->roles;
    }

    public function updateUserRolePermissions()
    {

    }



    public function inviteRegister($attr)
    {
        $invite = Invite::where('token', $attr['token'])->first();

        if($invite->email === $attr['email']) {

            if ($invite) {

                $inviting = Inviting::where('token', $attr['token'])->first();

                $data = json_decode($inviting['user_access']);

                $permissions = [];

                if (!empty($data->permission_id)) {
                    foreach ($data->permission_id as $key => $permission_id) {

                        $name = Permission::find($permission_id);

                        $permissions[] = $name['name'];
                    }
                }

                if (!empty($invite->id)) {

                    $owner = User::find($inviting->inviting_user_id);
                    $user = User::create([
                        'name' => $attr['name'],
                        'email' => $attr['email'],
                        'password' => Hash::make($attr['password']),
                        'active' => true,
                        'coupon' => $attr['coupon'],
                        'quantity' => 1,
                        'account_id' => $owner->account_id
                    ]);

                    if ($user) {

                        UserProfile::create([
                            'user_id' => $user->id,
                            'name' => $attr['name']
                        ]);

                        $inviting->update([
                            'invited_user_id' => $user->id,
                            'status' => 'active'
                        ]);

                        $role = Role::find($data->role_id);

                        $user->assignRole($role->name);

                        if (!empty($data->farm_id)) {
                            $user->farms()->attach($data->farm_id);
                        }

                        if (!empty($data->line_id)) {
                            $user->lines()->attach($data->line_id);
                        }

                        if (!empty($permissions)) {
                            foreach ($permissions as $key => $permission) {
                                $user->givePermissionTo($permission);
                            }
                        }
                    }
                    return response()->json(['status' => 'success'], 200);
                }
            } else {
                return response()->json(['status' => 'Token is invalid or expire'], 404);
            }
        } else {
            return response()->json(['status' => 'Error',
                                        'message' => 'Invited user has another email address'], 404);
        }
    }

    public function getTokens(OClient $oClient, $email, $password)
    {
        $oClient = OClient::where('password_client', 1)->first();

        $http = new Client;

        try {
//            env('API_URL') http://nginx/oauth/token
            $response = $http->post(config('services.api.api_url'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'username' => $email,
                    'password' => $password,
                    'scope' => '',
                ],
            ]);

            $result = json_decode((string)$response->getBody(), true);

            $result['xero'] = auth()->user()->getAccount()->xero_access_token ? true : false;
            return response(['status'=> 'Success',
                             'message' => 'User sign in',
                             'user_id' => auth()->user()->id,
                             'data' => $result
                            ], 200);

        } catch (Exception $e) {

            return response()->json(["message" => "unauthorized"], 401);

        }
    }

    public function refreshToken($request)
    {
        return $this->rt($request);
    }

//
    public function rt($request)
    {
        $refresh_token = $request->header('Refreshtoken');

        $user_id = $request->header('User');

        $oClient = OClient::where('password_client', 1)->first();

        $http = new Client;

        try {
            $response = $http->post(config('services.api.api_url'), [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token,
                    'client_id' => $oClient->id,
                    // 'client_secret' => $oClient->secret,
                    'client_secret' => '1WaRtPmD7dN8Pujxz7sYSHa9dIBPTipqgymfJElD',
                    'scope' => '',
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);
            $user = User::find($user_id);
            $result['xero'] = $user->getAccount()->xero_access_token ? true : false;

            return response(['status'=> 'Success',
                             'message' => 'User sign in',
                             'user_id' => $user_id,
                             'data' => $result], 200);

        } catch (Exception $e) {

            return response()->json(["message" => "unauthorized"], 401);

        }
    }
}


