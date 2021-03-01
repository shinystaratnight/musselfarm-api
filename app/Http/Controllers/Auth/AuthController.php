<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\SigninUserRequest;
use App\Http\Requests\Auth\SignupUserRequest;
use App\Http\Requests\Auth\ResendEmailRequest;
use App\Models\Invite;
use App\Repositories\Auth\AuthRepositoryInterface as AuthRepo;
use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponser;

    private $authRepo;

    public function __construct(AuthRepo $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function login(SigninUserRequest $request)
    {
        $attr = $request->validated();

        return $this->authRepo->login($attr);
    }

    public function signupUser(SignupUserRequest $request)
    {
        $attr = $request->validated();

        return $this->authRepo->registerUser($attr);
    }

    public function user()
    {
        return $this->success(Auth::user());
    }

    public function logout()
    {
        Auth::user()->token()->revoke();

        return $this->success('User Logged Out', 200);
    }

    public function signupActivate($token)
    {
        return $this->authRepo->signupActivate($token);
    }

    public function resendEmail(ResendEmailRequest $request)
    {
        $attr = $request->validated();

        return $this->authRepo->resend($attr);
    }

    public function invitationRedirect()
    {
        $inviteData = request()->all();

        $now = Carbon::now()->timestamp;

        $tokenCheck = Invite::where('token', $inviteData['token'])->first();

        if(($inviteData['expires'] >= $now) && !empty($tokenCheck->id)) {

            return redirect(config('services.api.front_end_url') . '/sign-up' . '?token=' . $inviteData['token'] . '&email=' . $inviteData['email']);

        } else {
            return response()->json(['status' => 'Error',
                                     'message' => 'Invitation has expired or has wrong token'], 404);
        }

    }

//    public function invitedSignup(Request $request)
//    {
//        dd($request->all());
//    }

    public function invitedPersonRegistration(Request $request)
    {
        return $this->authRepo->inviteRegister($request);
    }

    public function refresh(Request $request)
    {
        return $this->authRepo->refreshToken($request);
    }

    public function tokenExpired(Request $request)
    {
        return response()->json(['message' => 'Unauthenticated.'], 200);
    }
}
