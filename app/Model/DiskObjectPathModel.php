<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DiskObjectPathModel extends Model
{
    protected $primaryKey = "id";
    protected $table = "disk_object_path";

    public $timestamps = false;
}
