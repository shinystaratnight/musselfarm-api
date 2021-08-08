<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\User\UserResource;
use App\Http\Requests\Invite\UserInviteRequest;
use App\Http\Requests\Invite\DeactivateInviteUserRequest;
use App\Http\Resources\Invited\RolePermResource;
use App\Http\Resources\Invited\InvitedUserResource;
use App\Http\Requests\Permission\UpdateRolePermissionRequest;
use App\Models\User;
use App\Models\Invite;
use App\Models\Account;
use App\Models\Inviting;
use App\Models\AccountUser;
use App\Models\UserProfile;
use App\Notifications\InviteNotification;
use App\Repositories\Auth\InvitationRepositoryInterface as Invitation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    private $inviteRepo;

    public function __construct(Invitation $invite)
    {
        $this->inviteRepo = $invite;
    }

    public function index(Request $request)
    {
        return InvitedUserResource::collection(
            Account::find($request->input('account_id'))->users
        );
    }

    public function destroyPendingUser(Request $request)
    {
        if(filter_var($request->attr, FILTER_VALIDATE_EMAIL)) {

            $invite = Invite::where('email', $request->attr)->where('inviting_account_id', $request->input('account_id'))->first();

            if($invite) {
                $invite->delete();
            }

            $inviting = Inviting::where('email', $request->attr)->where('inviting_account_id', $request->input('account_id'))->first();

            if($inviting) {
                $inviting->delete();
            }

            return response()->json(['status' => 'success'], 200);

        } elseif(is_numeric($request->attr)) {

            $user = User::find($request->attr);

            if($user) {
                $user->forceDelete();

                $invite = Invite::where('email', $user->email)->where('inviting_account_id', $request->input('account_id'))->first();

                if ($invite) {
                    $invite->delete();
                }
            }

            return response()->json(['status' => 'success'], 200);

        } else {

            return response()->json(['message' => 'Invalid user data'], 404);

        }
    }

    public function deactivate(DeactivateInviteUserRequest $request)
    {
        $attr = $request->validated();

        $user = User::find($attr['user_id']);

        if(
            (auth()->user()->getAccount($attr['account_id'])->pivot->roles[0]['name'] === 'admin') &&
            ($user->getAccount($attr['account_id'])->pivot->roles[0]['name'] === 'admin')
        ) {
            return response()->json(['message' => 'Admin cannot deactivate admin'], 404);
        } else {
            Inviting::where('invited_user_id', $user->id)->update(['status' => 'deactivated']);

            $user->delete();

            return response()->json(['message' => 'success'], 200);
        }
    }

    public function activateDeactivatedUser(DeactivateInviteUserRequest $request)
    {
        $attr = $request->validated();

        User::withTrashed()->where('id', $attr['user_id'])->restore();

        UserProfile::withTrashed()->where('user_id', $attr['user_id'])->restore();

        Inviting::where('invited_user_id', $attr['user_id'])->where('inviting_account_id', $attr['account_id'])->update(['status' => 'active']);

        return response()->json(['message' => 'success'],200);
    }

    public function processInvites(UserInviteRequest $request)
    {
        $attr = $request->validated();

        return $this->inviteRepo->invite($attr);
    }

    public function getInviters()
    {
        $inviters = Inviting::where('invited_user_id', auth()->user()->id)->get()->toArray();

        $userList = [];
        foreach ($inviters as $inviter) {
            $user = User::find($inviter['inviting_user_id']);
            $userList[] = [
                'email' => $user['email'],
                'role' => auth()->user()->getAccount($inviter['inviting_account_id'])->pivot->roles[0]['name'],
                'id' => $user['id'],
                'acc_id' => $inviter['inviting_account_id']
            ];
        }

        if (!$inviters) {
            $acc = Account::where('owner_id', auth()->user()->id);
            $userList[] = [
                'email' => auth()->user()->email,
                'role' => auth()->user()->getAccount($acc->id)->pivot->roles[0]['name'],
                'id' => auth()->user()->id,
                'acc_id' => $acc->id
            ];
        }
        return response()->json(['inviters' => $userList], 200);
    }

    public function getPermissions(DeactivateInviteUserRequest $request)
    {
        $attr = $request->validated();

        $user = User::where('id', $attr['user_id'])->first();

        return new RolePermResource($user);
    }

    public function permissionsUpdate(UpdateRolePermissionRequest $request)
    {
        $attr = $request->validated();

        $permissions = [];

        $user = User::where('id', $attr['user_id'])->first()->getAccount($attr['account_id']);

        if ($user) {

            if (!empty($attr['permission_id'])) {
                foreach ($attr['permission_id'] as $key => $permission_id) {

                    $name = Permission::find($permission_id);

                    $permissions[] = $name['name'];
                }
            }

            if (isset($attr['role_id'])) {
                $role = Role::find($attr['role_id']);

                $user->pivot->syncRoles($role);
            }

            $inviting = Inviting::where('inviting_account_id', $attr['account_id'])->where('invited_user_id', $attr['user_id'])->first();

            $user_access = json_decode($inviting->user_access);
            if (isset($attr['role_id'])) {
                $user_access->role_id = $attr['role_id'];
            }

            $user_access->farm_id = $attr['farm_id'];
            $user_access->line_id = $attr['line_id'];
            $user_access->permission_id = $attr['permission_id'];
            
            $inviting->user_access = json_encode($user_access);
            $inviting->save();

            $user->pivot->user_access = json_encode($user_access);
            $user->pivot->save();

            return response()->json(['message' => 'success'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }

    }
}
