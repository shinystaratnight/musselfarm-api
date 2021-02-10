<?php

namespace App\Repositories\UserProfile;

use App\Http\Resources\User\UserProfileResource;
use App\Models\UserProfile;
use App\Repositories\UserProfile\UserProfileRepositoryInterface as Profile;
use Illuminate\Support\Facades\Storage;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    private $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    public function createProfile($attr)
    {
        $profile = UserProfile::created([
           'user_id' => $attr['user_id'],
           'phone_number' => $attr['phone_number'],
           'company_name' => $attr['company_name'],
           'company_address' => $attr['company_address'],
        ]);

        return UserProfileResource::collection($profile);
    }

    public function upload($attr)
    {
        $path = $attr->file('image')->store('avatars');

        if($path) {
            UserProfile::where('user_id', $attr->user_id)->update(['avatar' => env('APP_URL') . Storage::path($path)]);

            $profile = UserProfile::where('user_id', $attr->user_id)->first();

            return new UserProfileResource($profile);
        }
    }
}
