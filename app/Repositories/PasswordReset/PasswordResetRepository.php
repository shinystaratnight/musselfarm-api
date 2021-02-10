<?php

namespace App\Repositories\PasswordReset;

use App\Models\PasswordReset;
use App\Models\User;
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

            $user->save();

            $passwordReset->delete();

            $user->notify(new PasswordResetSuccess($passwordReset));

            return response()->json($user);
        }
    }
}
