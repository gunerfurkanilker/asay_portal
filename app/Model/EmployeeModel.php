<?php

namespace App\Model;

use http\Client\Request;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Employee";
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';




    public static function saveGeneralInformations($employee,$requestData)
    {
        $employee->FirstName = $requestData['firstname'];
        $employee->LastName = $requestData['lastname'];
        $employee->AccessTypeID = $requestData['accesstypeid'];
        $employee->Domain = $requestData['domain'];
        $employee->JobEmail = $requestData['jobemail'];
        $employee->JobMobilePhone = $requestData['jobphone'];
        $employee->InterPhone = $requestData['internalphone'];
        $employee->ContractTypeID = $requestData['contracttypeid'];
        $employee->StartDate = $requestData['jobbegindate'];
        $employee->ContractFinishDate = $requestData['contractfinishdate'];
        $employee->WorkingScheduleID = $requestData['workingscheduleid'];

        return $employee->save();
    }



}
