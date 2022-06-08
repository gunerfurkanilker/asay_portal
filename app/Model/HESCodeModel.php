<?php

namespace App\Model;

use App\Library\Asay;
use Illuminate\Database\Eloquent\Model;

class HESCodeModel extends Model
{
    //
    protected $table = "HESCodes";
    protected $guarded=[];
    protected $appends = [
        'Employee', 'departmentt','IsActive'
    ];

    public function getEmployeeAttribute(){

        $employee = $this->hasOne(EmployeeModel::class,"Id","EmployeeID");
        if ($employee)
        {
            $employee = $employee->where("Active",1)->first();
            return $employee;
        }
        else
            return null;
    }

    public function getDepartmenttAttribute()
    {
        return $this->employee->employeeposition->Department->Sym ?? '';
    }
    public function getIsActiveAttribute(){

        if($this->Active == 1)
        {
            return "Aktif Çalışan";
        }else{
            return "Aktif Çalışan Değil";
        }
    }


}
