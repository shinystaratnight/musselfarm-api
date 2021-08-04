<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;

class FarmUtil extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'type', 'account_id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
