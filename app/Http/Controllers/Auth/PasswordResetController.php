<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordReset\PasswordCreateRequest;
use App\Http\Requests\PasswordReset\PasswordResetRequest;
use App\Repositories\PasswordReset\PasswordResetRepositoryInterface;
//use Illuminate\Http\Request;
//use Carbon\Carbon;
//use App\Notifications\PasswordResetSuccess;
//use App\Models\User;
//use Illuminate\Support\Str;
//use App\Models\PasswordReset;

class PasswordResetController extends Controller
{
    private $resetRepo;

    public function __construct(PasswordResetRepositoryInterface $resetRepo)
    {
        $this->resetRepo = $resetRepo;
    }

    public function create(PasswordCreateRequest $request)
    {
        $attr = $request->validated();

        return $this->resetRepo->newPassword($attr);
    }

    public function find($token)
    {
        return $this->resetRepo->getToken($token);
    }

    public function reset(PasswordResetRequest $request)
    {
        $attr = $request->validated();

        return $this->resetRepo->resetPassword($attr);
    }
}
