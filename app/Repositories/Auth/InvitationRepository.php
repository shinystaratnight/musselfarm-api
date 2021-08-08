<?php

namespace App\Repositories\Auth;

use App\Models\Invite;
use App\Models\Inviting;
use App\Models\Account;
use App\Models\User;
use App\Notifications\InviteNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class InvitationRepository implements InvitationRepositoryInterface
{
    public function invite($attr)
    {
        do {
            $token = Str::random(20);
        } while (Invite::where('token', $token)->first());

        $inviteExist = Invite::where([
            'email' => $attr['email'],
            'inviting_account_id' => $attr['account_id']
        ])->first();

        $inviting = null;
        if ($inviteExist) {
            return response()->json(['message' => 'This user is already invited'], 404);
        }
        if (User::where('email', $attr['email'])->first()) {
            try {
                $invite = Invite::create([
                    'token' => $token,
                    'inviting_account_id' => $attr['account_id'],
                    'email' => $attr['email']
                ]);

                $inviting = Inviting::create([
                    'email' => $invite['email'],
                    'token' => $invite['token'],
                    'invited_user_id' => null,
                    'inviting_user_id' => Account::find($attr['account_id'])->owner_id,
                    'inviting_account_id' => $attr['account_id'],
                    'user_access' => json_encode($attr)
                ]);

                $url = URL::temporarySignedRoute('inviting-to-exist-user',
                                                Carbon::now()->addDays(7),
                                                ['token' => $token, 'email' => $attr['email']]);

                return response()->json(['status' => $url], 200);

                // Notification::route('mail', $attr['email'])->notify(new InviteNotification($url));

                // return response()->json(['status' => 'Success'], 200);
            } catch(\Exception $e) {

                $invite->delete();

                $inviting->delete();

                return response()->json(['message' => 'Invitation cannot be send'], 404);

            }
        } else {

            try {
                $invite = Invite::create([
                    'token' => $token,
                    'inviting_account_id' => $attr['account_id'],
                    'email' => $attr['email']
                ]);

                $inviting = Inviting::create([
                    'email' => $attr['email'],
                    'token' => $token,
                    'invited_user_id' => null,
                    'inviting_user_id' => Account::find($attr['account_id'])->owner_id,
                    'inviting_account_id' => $attr['account_id'],
                    'user_access' => json_encode($attr)
                ]);

                $url = URL::temporarySignedRoute('inviting',
                                                Carbon::now()->addDays(7),
                                                ['token' => $token, 'email' => $attr['email']]);

                return response()->json(['status' => $url], 200);

                // Notification::route('mail', $attr['email'])->notify(new InviteNotification($url));

                // return response()->json(['status' => 'Success'], 200);

            } catch(\Exception $e) {

                $invite->delete();

                $inviting->delete();

                return response()->json(['message' => 'Invitation cannot be send'], 404);

            }
        }
    }
}
