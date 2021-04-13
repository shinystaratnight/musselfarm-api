<?php

namespace App\Repositories\PasswordReset;

use App\Models\PasswordReset;
use App\Models\User;
use App\Models\Account;
use App\Models\FarmUtil;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    public function newPassword($attr)
    {
        $user = User::where('email', $attr['email'])->first();

        if (!$user)
        {
            return response()->json([
                'message' => 'We cannot find a user with that e-mail address.'
            ], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $attr['email']],
            [
                'email' => $user->email,
                'token' => Str::random(60)
            ]
        );

        if ($user && $passwordReset)
        {
            $user->notify(
                new PasswordResetRequest($passwordReset->token, $passwordReset->email)
            );
        }

        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);
    }

    public function getToken($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset)
        {
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast())
        {
            $passwordReset->delete();

            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        return response()->json($passwordReset);
    }

    public function resetPassword($attr)
    {
        $passwordReset = PasswordReset::where([['token', $attr['token']], ['email', $attr['email']]])->first();

        if (!$passwordReset) {
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'We cannot find a user with that e-mail address.'
            ], 404);

        } else {

            $user->password = bcrypt($attr['password']);

            if ($user->active) {
                $user->active = true;
                $user->activation_token = '';

                $trialDays = config('services.stripe.stripe_trial');
                if ($user->coupon == 'coupon1') $trialDays = 30;
                else if ($user->coupon == 'coupon2') $trialDays = 60;
                $user->trial_ends_at = now()->addDays($trialDays);

                $user->quantity = 1;

                $account = Account::create([]);
                $user->account_id = $account->id;

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
            }

            $user->save();
            
            $passwordReset->delete();

            $user->notify(new SignupActivate($user));
            $user->notify(new PasswordResetSuccess($passwordReset));

            return response()->json($user);
        }
    }
}
