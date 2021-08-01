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
        return $this->belongsToMany(User::class)->withPivot('id', 'user_access')->using('App\Models\AccountUser');
    }

    public function farms()
    {
        return $this->hasMany(Farm::class);
    }

    public function getUserFarms($userId)
    {
        $access = json_decode($this->users()->where('user_id', $userId)->first()->pivot->user_access);
        if ($access) {
            if ($this->users()->where('user_id', $userId)->first()->pivot->hasRole('admin')) {
                return $this->farms;        
            }
            $fa_access = $access->farm_id;
            return $this->farms->whereIn('id', $fa_access);
        }
        return $this->farms;
    }
}
