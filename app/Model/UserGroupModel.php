<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserGroupModel extends Model
{
    protected $table = "user_groups";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
