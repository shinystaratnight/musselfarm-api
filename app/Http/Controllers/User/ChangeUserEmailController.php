<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Repositories\User\ChangeEmailRepositoryInterface as Mail;
use http\Env\Request;
use Illuminate\Support\Facades\Hash;

class ChangeUserEmailController extends Controller
{
    private $emailRepo;

    public function __construct(Mail $mail)
    {
        $this->emailRepo = $mail;
    }


    public function changeEmail(ChangeEmailRequest $request)
    {
        $attr = $request->validated();

        return $this->emailRepo->emailChangeRequest($attr);
//        return $this->emailRepo->email($attr);
    }

    public function apply()
    {
        return $this->emailRepo->applyNewEmail();
    }
}
