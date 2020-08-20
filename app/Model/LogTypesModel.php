<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogTypesModel extends Model
{
    protected $table = "log_types";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];
}
