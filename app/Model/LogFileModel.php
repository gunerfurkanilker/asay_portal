<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogFileModel extends Model
{
    protected $table = "LogFile";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];
}
