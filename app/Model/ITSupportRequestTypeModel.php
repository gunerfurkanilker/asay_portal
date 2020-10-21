<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ITSupportRequestTypeModel extends Model
{
    protected $table = "ITSupportRequestType";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
