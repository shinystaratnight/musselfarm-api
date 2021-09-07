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
use App\Models\Farm;
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
            return $this->getTokens($oClient, $attr['email'], $attr['password'], $attr['remember']);

        } else {

            return response(['message' => 'Account not activated, please activate account or reset the password to activate it'], 401);

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
        $account->owner_id = $user->id;
        $account->save();
        $user->account_id = $account->id;
        $user->accounts()->attach($account->id);
        $user->save();

        // Set Default Farms Util
        // $defaultFarmsUtilData = [
        //     ['name' => 'D-Seed1', 'type' =>'seed'],
        //     ['name' => 'D-Seed2', 'type' =>'seed'],
        //     ['name' => 'D-Seed3', 'type' =>'seed'],
        //     ['name' => 'D-Maintenance1', 'type' =>'maintenance'],
        //     ['name' => 'D-Maintenance2', 'type' =>'maintenance'],
        //     ['name' => 'D-Maintenance3', 'type' =>'maintenance'],
        // ];
        // $farmUtils = array_map(function ($util) use ($user, $account) {
        //     $util['user_id'] = $user->id;
        //     $util['account_id'] = $account->id;
        //     return $util;
        // }, $defaultFarmsUtilData);

        // FarmUtil::insert($farmUtils);

        $ua_pivot = $user->getAccount($account->id)->pivot;

        $ua_pivot->assignRole('owner');
        $ua_pivot->givePermissionTo('view');
        $ua_pivot->givePermissionTo('edit');
        $ua_pivot->givePermissionTo('finance');


        return redirect( config('services.api.front_end_url') . '/sign-in/checked');
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

                    $acc_id = $inviting['inviting_account_id'];

                    $user = User::create([
                        'name' => $attr['name'],
                        'email' => $attr['email'],
                        'password' => Hash::make($attr['password']),
                        'active' => true,
                        'coupon' => $attr['coupon'],
                        'quantity' => 1,
                        'account_id' => $acc_id
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

                        $user->accounts()->attach($acc_id);

                        $ua_pivot = $user->getAccount($acc_id)->pivot;
                        $ua_pivot->user_access = $inviting['user_access'];
                        $ua_pivot->save();

                        $role = Role::find($data->role_id);
                        $ua_pivot->assignRole($role->name);

                        if (!empty($data->farm_id)) {
                            Account::find($acc_id)->farms()->saveMany(Farm::whereIn('id', $data->farm_id)->get());
                        }

                        if (!empty($permissions)) {
                            foreach ($permissions as $key => $permission) {
                                $ua_pivot->givePermissionTo($permission);
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

    public function inviteAccept($attr)
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

                    $acc_id = $inviting['inviting_account_id'];

                    $invited_user = User::where('email', $attr['email'])->first();
                    $invited_user->accounts()->attach($acc_id);

                    $inviting->update([
                        'invited_user_id' => $invited_user->id,
                        'status' => 'active'
                    ]);

                    $ua_pivot = $invited_user->getAccount($acc_id)->pivot;
                    $ua_pivot->user_access = $inviting['user_access'];
                    $ua_pivot->save();

                    $role = Role::find($data->role_id);
                    $ua_pivot->assignRole($role->name);

                    if (!empty($data->farm_id)) {
                        Account::find($acc_id)->farms()->saveMany(Farm::whereIn('id', $data->farm_id)->get());
                    }

                    if (!empty($permissions)) {
                        foreach ($permissions as $key => $permission) {
                            $ua_pivot->givePermissionTo($permission);
                        }
                    }
                }
            }
        }
    }

    public function getTokens(OClient $oClient, $email, $password, $remember)
    {
        $oClient = OClient::where('password_client', 1)->first();

        $http = new Client;

        try {
//            env('API_URL') http://nginx/oauth/token
            $response = $http->post(config('services.api.api_url'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'remember' => $remember,
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
                    'client_secret' => $oClient->secret,
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
