<?php

namespace App\Repositories\UserProfile;

interface UserProfileRepositoryInterface
{
    public function createProfile($attr);

    public function uploadAvatar($attr);
}
