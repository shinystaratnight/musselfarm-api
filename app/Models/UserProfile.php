<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'company_name', 'company_address', 'phone_number', 'avatar'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
