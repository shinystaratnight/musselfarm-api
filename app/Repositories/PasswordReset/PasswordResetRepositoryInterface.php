<?php

namespace App\Repositories\PasswordReset;

interface PasswordResetRepositoryInterface
{
    public function newPassword($attr);

    public function getToken($token);

    public function resetPassword($attr);
}
