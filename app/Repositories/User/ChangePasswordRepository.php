<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePasswordRepository implements ChangePasswordRepositoryInterface
{
    public function changePassword($attr)
    {
        if(auth()->check()) {
            if (Hash::check($attr['password'], auth()->user()->password)){

                $user = auth()->user();

                $user->password = Hash::make($attr['new_password']);

                $user->save();

                return response()->json(['status' => 'Success'],200);
            } else {
                return response()->json(['status' => 'Error',
                                         'message' => 'Current password is invalid'], 400);
            }
        }
    }
}
