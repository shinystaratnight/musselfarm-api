<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['xero_access_token',
                           'tenant_id',
                            'client_id',
                            'client_secret',
                            'redirect_url',
                            'owner_id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'account_user', 'account_id', 'user_id')->withPivot('id', 'user_access')->using('App\Models\AccountUser');
    }

    public function farms()
    {
        return $this->hasMany(Farm::class);
    }

    public function getUserAccess($userId)
    {
        $access = $this->users()->where('user_id', $userId)->first()->pivot->user_access;
        if ($access) {
            return json_decode($access);
        }
        return '';
    }

    public function getUserFarms($userId)
    {
        $access = json_decode($this->users()->where('user_id', $userId)->first()->pivot->user_access);
        if ($access) {
            if ($this->users()->where('user_id', $userId)->first()->pivot->hasRole('admin')) {
                return $this->farms;
            }
            $faAccess = $access->farm_id;
            return $this->farms->whereIn('id', $faAccess);
        }
        return $this->farms;
    }
}
