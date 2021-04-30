<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class HealthReportModel extends Model
{
    protected $table = "HealthReports";
    protected $appends = [
        'Employee',
        'DocumentType'
    ];

    public function getEmployeeAttribute()
    {

        $employee = $this->hasOne(EmployeeModel::class,"Id","EmployeeID");
        if ($employee)
            return $employee->where("Active",1)->first();
        else
            return null;

    }

    public function getDocumentTypeAttribute()
    {

        $docType = $this->hasOne(HealthReportTypeModel::class,"id","DocumentTypeID");
        if ($docType)
            return $docType->where("Active",1)->first();
        else
            return null;

    }
}
