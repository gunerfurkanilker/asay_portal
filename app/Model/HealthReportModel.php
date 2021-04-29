<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class HealthReportModel extends Model
{
    protected $table = "HealthReports";
    protected $appends = [
        'Employee'
    ];

    public function getEmployeeAttribute()
    {

        $employee = $this->hasOne(EmployeeModel::class,"Id","EmployeeID");
        if ($employee)
            return $employee->where("Active",1)->first();
        else
            return null;

    }
}
