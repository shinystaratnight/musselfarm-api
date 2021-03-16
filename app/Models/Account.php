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
                            'redirect_url'];

}
