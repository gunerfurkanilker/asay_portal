<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ObjectTypeModel extends Model
{
    protected $primaryKey = "id";
    protected $table = 'ObjectTypes';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [];


}
