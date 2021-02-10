<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seed extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function lines()
    {
        return $this->belongsTo(Line::class);
    }

    public function harvests()
    {
        return $this->belongsTo(HarvestGroup::class);
    }
}
