<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Automation extends Model
{
    use HasFactory;

    protected $fillable = ['condition',
                           'action',
                            'time',
                            'title',
                            'description',
                            'creator_id'];

}
