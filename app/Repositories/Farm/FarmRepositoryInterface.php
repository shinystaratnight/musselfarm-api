<?php

namespace App\Repositories\Farm;

interface FarmRepositoryInterface
{
    public function farms($user);

    public function createFarm($attr);
}
