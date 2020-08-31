<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DiskObjectModel extends Model
{
    protected $primaryKey = "id";
    protected $table = "disk_object";

    public $timestamps = false;
}
