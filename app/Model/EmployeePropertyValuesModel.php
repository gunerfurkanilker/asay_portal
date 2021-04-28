<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeePropertyValuesModel extends Model
{
    protected $table = "logs";

    public $timestamps = false;
    protected $fillable = ['EmployeeID','PropertyCode','PropertyType'];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];

    public function setPropertyValues($employeeId,$propertyCode,$propertyValue,$PropertyType="Text")
    {
        $FlowPropertValues = self::firstOrNew(["FlowId"=>$employeeId,"PropertyCode"=>$propertyCode,"PropertyType"=>$PropertyType]);
        $FlowPropertValues->ValueString = $propertyValue;

        if($FlowPropertValues->save())
            return true;
        else
            return false;

    }
}
