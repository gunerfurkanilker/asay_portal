<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ActivityStreamModel extends Model
{
    protected $table = "activity_stream";
    protected $appends =[
        "user"
    ];


    public function getUserAttribute()
    {
        return $this->hasMany(UserModel::class, "EmployeeID", "EmployeeID")->first();
    }
}
