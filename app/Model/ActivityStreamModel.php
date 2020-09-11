<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ActivityStreamModel extends Model
{
    protected $table = "activity_stream";
    protected $appends =[
        "employee"
    ];


    public function getEmployeeAttribute()
    {
        return $this->hasMany(EmployeeModel::class, "Id", "EmployeeID")->first();
    }
}
