<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ParametersModel extends Model
{
    protected $table = "parameters";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];

}
