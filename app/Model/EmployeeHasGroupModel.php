<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeHasGroupModel extends Model
{
    protected $table = "EmployeeHasGroup";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
