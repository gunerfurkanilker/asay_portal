<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserGroupModel extends Model
{
    protected $table = "user_group";

    public $timestamps = false;
    protected $fillable = ["group_id","user_id"];

    protected $hidden = [];
    protected $casts = [];
}
