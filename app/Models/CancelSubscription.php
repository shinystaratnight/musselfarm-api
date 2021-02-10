<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelSubscription extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'stripe_id', 'cancel_at', 'cancel_at_period_end', 'canceled_at'];
}
