<?php

namespace App\Repositories\Farm;

interface FarmRepositoryInterface
{
    public function farms($user);

    public function createFarm($attr);

    public function lineSorting($request);

    public function manipulateJson($json,$farmId,$name,$order);

    public function getLineSorting($request);
}
