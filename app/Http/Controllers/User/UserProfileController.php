<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\Profile\CreateUserProfileRequest;
use App\Http\Requests\Profile\UpdateUserProfileRequest;
use App\Http\Requests\Profile\UploadAvatarProfileRequest;
use App\Http\Resources\User\UpdateUserProfileResource;
use App\Http\Resources\User\UserProfileResource;
use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function index()
    {
        $profile = UserProfile::all();

        return UserProfileResource::collection($profile);
    }

    public function store(CreateUserProfileRequest $request)
    {
        $profile = UserProfile::create($request->validated());

        return new UserProfileResource($profile);
    }

    public function show(UserProfile $profile)
    {
        return new UserProfileResource($profile);
    }

    public function update(UpdateUserProfileRequest $request, UserProfile $profile)
    {
        $profile->update($request->validated());

        return new UserProfileResource($profile);
    }

    public function uploadAvatar(UploadAvatarProfileRequest $request)
    {
        $request->validated();

        $file = $request->file('image');
        $path = $file->hashName('public/avatars');
        $image = Image::make($file)->fit(70);
        Storage::put($path, (string) $image->encode());

        $url = Storage::url($path);

        if($url) {
            UserProfile::where('user_id', $request->user_id)->update(['avatar' => $url]);

            $profile = UserProfile::where('user_id', $request->user_id)->first();

            return new UserProfileResource($profile);
        }
    }

    public function getEmailAddress()
    {
        return response()->json(['email' => auth()->user()->email], 200);
    }

    public function getSuccess()
    {
        return response()->json(['status' => 'Success'], 200);
    }
}
