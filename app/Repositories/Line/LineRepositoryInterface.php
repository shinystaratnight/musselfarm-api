<?php

namespace App\Repositories\Line;

interface LineRepositoryInterface
{
    public function createLine($attr);

    public function editLine($attr);
}
