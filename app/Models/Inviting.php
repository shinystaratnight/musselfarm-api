<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inviting extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'status', 'token', 'user_access', 'inviting_user_id', 'invited_user_id'];

    public function users()
    {
        return $this->belongsTo(User::class, 'invited_user_id', 'id')->withTrashed();
    }
}
