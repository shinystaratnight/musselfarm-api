<?php

namespace App\Repositories\User;

use App\Repositories\User\UserRepositoryInterface as User;

class UserRepository implements UserRepositoryInterface
{
    private $userRepo;

    public function __construct(User $user)
    {
        $this->userRepo = $user;
    }

    public function getUsers()
    {

    }

    public function getUser($id)
    {

    }
}
