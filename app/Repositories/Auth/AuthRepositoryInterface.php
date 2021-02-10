<?php

namespace App\Repositories\Auth;

interface AuthRepositoryInterface
{
    public function registerUser($attr);

    public function login($attr);

    public function signupActivate($token);

    public function inviteRegister($attr);

    public function resend($attr);
}
