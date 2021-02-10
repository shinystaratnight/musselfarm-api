<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function getUsers();

    public function getUser($id);
}
