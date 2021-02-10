<?php

namespace App\Repositories\Auth;

use App\Models\Invite;
use App\Models\Inviting;
use App\Notifications\InviteNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class InvitationRepository implements InvitationRepositoryInterface
{
    public function invite($attr)
    {
        try {
            do {
                $token = Str::random(20);
            } while (Invite::where('token', $token)->first());
            $invite = Invite::create([
                'token' => $token,
                'email' => $attr['email']
            ]);

            if (auth()->user()->roles[0]['name'] !== 'owner') {

                $inviting = Inviting::create([
                    'email' => $invite['email'],
                    'token' => $invite['token'],
                    'invited_user_id' => null,
                    'inviting_user_id' => auth()->user()->getOwner()->id,
                    'user_access' => json_encode($attr)
                ]);

            } else {

                $inviting = Inviting::create([
                    'email' => $invite['email'],
                    'token' => $invite['token'],
                    'invited_user_id' => null,
                    'inviting_user_id' => auth()->user()->id,
                    'user_access' => json_encode($attr)
                ]);
            }

            $url = URL::temporarySignedRoute('inviting',
                                             Carbon::now()->addDays(7),
                                             ['token' => $token, 'email' => $attr['email']]);

            Notification::route('mail', $attr['email'])->notify(new InviteNotification($url));



            return response()->json(['status' => 'Success'], 200);
        } catch(\Exception $e) {

            $invite->delete();

            $inviting->delete();

            return response()->json(['message' => 'Invitation cannot be send'], 404);
        }
    }
}
