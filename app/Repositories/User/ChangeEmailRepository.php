<?php

namespace App\Repositories\User;

use App\Models\ChangeEmail;
use App\Models\User;
use App\Notifications\ChangeEmailNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;


class ChangeEmailRepository implements ChangeEmailRepositoryInterface
{
    public function email($attr)
    {
        if (auth()->check()) {
            if (Hash::check($attr['password'], auth()->user()->password)) {

                $user = auth()->user();

                $user->email = $attr['email'];

                $user->save();

                return response()->json(['status' => 'Success'], 200);
            } else {
                return response()->json(['status' => 'Error',
                                         'message' => 'Mismatch credentials'], 404);
            }
        }
    }

    public function emailChangeRequest($attr)
    {
        if (auth()->check()) {

            if (Hash::check($attr['password'], auth()->user()->password)) {

                $lastChangeRequest = ChangeEmail::where(['user_id' => auth()->user()->id, 'active' => false])->first();

                if($lastChangeRequest) {
                    $lastChangeRequest->delete();
                }

                $newMail = ChangeEmail::create([
                    'user_id' => auth()->user()->id,
                    'email' => $attr['email'],
                    'token' => bin2hex(random_bytes(20))
                ]);

                $url = URL::temporarySignedRoute('apply',
                    Carbon::now()->addDays(7),
                    ['token' => $newMail['token']]);

                Notification::route('mail', $newMail['email'])->notify(new ChangeEmailNotification($url));

                return response()->json(['status' => 'Success'], 200);

            } else {

                return response()->json(['status' => 'Error',
                                         'message' => 'Mismatch credentials'], 404);
            }
        }
    }

    public function applyNewEmail()
    {
        $newEmail = ChangeEmail::where('token', request()->token)->first();

        if(!empty($newEmail)) {

            $user = User::find($newEmail->user_id);

            $newEmail->active = true;

            $newEmail->save();

            $user->email = $newEmail->email;

            $user->save();

            $newEmail->delete();
        }

        return redirect(config('services.api.front_end_url') . '/sign-in');
    }

}
