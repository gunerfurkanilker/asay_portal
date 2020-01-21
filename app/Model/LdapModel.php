<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LdapModel extends Model
{
    protected $table = "ldaps";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
