<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Models\User;
use App\Repositories\User\ChangePasswordRepositoryInterface as ChangePassword;
use Illuminate\Http\Request;

class ChangeUserPasswordController extends Controller
{
    private $changePassRepo;

    public function __construct(ChangePassword $changePassword)
    {
        $this->changePassRepo = $changePassword;
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $attr = $request->validated();

        return $this->changePassRepo->changePassword($attr);
    }
}
