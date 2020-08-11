<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ObjectFileModel extends Model
{
    protected $primaryKey = "id";
    protected $table = 'ObjectFiles';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [];
    protected $fillable = ['ObjectType','ObjectId','Type'];


}
