<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeePropertyValuesModel extends Model
{
    protected $table = "EmployeePropertyValues";

    public $timestamps = false;
    protected $fillable = ['EmployeeID','PropertyCode','PropertyType','PropertyValue'];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];

    public static function setPropertyValues($employeeId,$propertyCode,$propertyValue,$PropertyType="Text")
    {
        $FlowPropertValues = self::firstOrNew(["EmployeeID"=>$employeeId,"PropertyCode"=>$propertyCode,"PropertyType"=>$PropertyType]);
        $FlowPropertValues->PropertyValue = $propertyValue;

        if($FlowPropertValues->save())
            return true;
        else
            return false;

    }
}
