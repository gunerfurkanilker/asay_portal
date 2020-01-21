<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupModel extends Model
{
    protected $table = "groups";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
