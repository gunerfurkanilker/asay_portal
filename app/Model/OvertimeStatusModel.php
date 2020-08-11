<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OvertimeStatusModel extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $table = 'OvertimeStatus';
}
