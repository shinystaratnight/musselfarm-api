<?php

namespace App\Repositories\User;

interface ChangeEmailRepositoryInterface
{
    public function email($attr);

    public function emailChangeRequest($attr);

    public function applyNewEmail();
}
