<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserFieldModel extends Model
{
    protected $table = "user_fields";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
