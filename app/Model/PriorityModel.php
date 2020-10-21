<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PriorityModel extends Model
{
    protected $table = "Priority";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
