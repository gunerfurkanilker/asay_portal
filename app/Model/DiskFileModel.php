<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DiskFileModel extends Model
{
    protected $primaryKey = "id";
    protected $table = "disk_file";

    public $timestamps = false;
}
