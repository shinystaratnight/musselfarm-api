<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\Invite\UserInviteRequest;
use App\Http\Requests\Invite\DeactivateInviteUserRequest;
use App\Http\Requests\Permission\UpdateRolePermissionRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\Invited\InvitedUserResource;
use App\Http\Resources\Invited\RolePermResource;
use App\Models\Invite;
use App\Models\Inviting;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\InviteNotification;
use App\Repositories\Auth\InvitationRepositoryInterface as Invitation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private $inviteRepo;

    public function __construct(Invitation $invite)
    {
        $this->inviteRepo = $invite;
    }

    public function index()
    {
        if(auth()->user()->roles[0]['name'] === 'owner') {
            $users = Inviting::with('users')->where('inviting_user_id', auth()->user()->id)->get();

            $o = User::where('id', auth()->user()->id)->first();

            $users[] = $o;

            return InvitedUserResource::collection($users);
        } else {
            $owner = Inviting::where('invited_user_id', auth()->user()->id)->first();

            $o = User::where('id', $owner['inviting_user_id'])->first();

            $users = Inviting::with('users')->where('inviting_user_id', $owner['inviting_user_id'])->get();

            $users[] = $o;

            return InvitedUserResource::collection($users);
        }
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }


    public function destroyPendingUser(Request $request)
    {
        if(filter_var($request->attr, FILTER_VALIDATE_EMAIL)) {

            $invite = Invite::where('email', $request->attr)->first();

            if($invite) {
                $invite->delete();
            }

            $inviting = Inviting::where('email', $request->attr)->first();

            if($inviting) {
                $inviting->delete();
            }

            return response()->json(['status' => 'success'], 200);

        } elseif(is_numeric($request->attr)) {

            $user = User::find($request->attr);

            if($user) {
                $user->forceDelete();

                $invite = Invite::where('email', $user->email)->first();

                $invite->delete();
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

        if((auth()->user()->roles[0]['name'] === 'admin') && ($user->roles[0]['name'] === 'admin')) {
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

        Inviting::where('invited_user_id', $attr['user_id'])->update(['status' => 'active']);

        return response()->json(['message' => 'success'],200);
    }

    public function processInvites(UserInviteRequest $request)
    {
        $attr = $request->validated();

        return $this->inviteRepo->invite($attr);
    }

    public function getPermissions(DeactivateInviteUserRequest $request)
    {
        $attr = $request->validated();

        $user = User::where('id', $attr['user_id'])->with(['roles', 'permissions', 'farms', 'lines'])->first();

        return new RolePermResource($user);
    }

    public function permissionsUpdate(UpdateRolePermissionRequest $request)
    {
        $attr = $request->validated();

        $permissions = [];

        $user = User::where('id', $attr['user_id'])->first();

        if ($user) {

            if (!empty($attr['permission_id'])) {
                foreach ($attr['permission_id'] as $key => $permission_id) {

                    $name = Permission::find($permission_id);

                    $permissions[] = $name['name'];
                }
            }

            if (isset($attr['role_id'])) {
                $role = Role::find($attr['role_id']);

                $user->syncRoles($role);
            }

            if (!empty($permissions)) {
                $user->syncPermissions($permissions);
            }

            if (isset($attr['farm_id'])) {
                $user->farms()->sync($attr['farm_id']);
            }
// TODO add detach for farms and lines
            if (isset($attr['line_id'])) {
                $user->lines()->sync($attr['line_id']);
            }
            return response()->json(['message' => 'success'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }

    }
}
