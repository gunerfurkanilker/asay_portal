<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserPropertyModel extends Model
{
    protected $table = "user_property";

    public $timestamps = false;
    protected $fillable = ['user_id','field'];

    protected $hidden = [];
    protected $casts = [];
}
