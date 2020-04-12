<?php

namespace App\Model;


use Carbon\Carbon;
use Carbon\Traits\Date;
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
        $employee->StartDate = new Carbon($requestData['jobbegindate']);
        $employee->ContractFinishDate = new Carbon($requestData['contractfinishdate']);
        $employee->WorkingScheduleID = $requestData['workingscheduleid'];

        return $employee->save();
    }

    public static function saveJobPosition($employee,$requestData)
    {
        $employee->CompanyID = $requestData['firstname'];
        $employee->CityID = $requestData['lastname'];
        $employee->DistrictID = $requestData['accesstypeid'];
        $employee->DepartmentID = $requestData['domain'];
        $employee->TitleID = $requestData['jobemail'];
        $employee->ManagerID = $requestData['jobphone'];
        $employee->WorkingTypeID = $requestData['internalphone'];
        $employee->PositionStartDate = $requestData['contracttypeid'];
        $employee->PositionEndDate = $requestData['jobbegindate'];
        $employee->ContractFinishDate = $requestData['contractfinishdate'];
        $employee->WorkingScheduleID = $requestData['workingscheduleid'];

        if ($employee->save)
            return $employee->fresh();
        else
            return false;

    }



}
