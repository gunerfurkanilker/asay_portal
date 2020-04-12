<?php

namespace App\Model;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Employee";
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';


    public function accesstype()
    {
        return $this->hasOne("App\Model\AccessTypeModel","Id","AccessTypeID");
    }




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

        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function saveJobPosition($employee,$requestData)
    {
        $employee->CompanyID = $requestData['companyid'];
        $employee->CityID = $requestData['cityid'];
        $employee->DistrictID = $requestData['districtid'];
        $employee->DepartmentID = $requestData['departmentid'];
        $employee->TitleID = $requestData['titleid'];
        $employee->ManagerID = $requestData['managerid'];
        $employee->WorkingTypeID = $requestData['workingtypeid'];
        $employee->PositionStartDate = new Carbon($requestData['positionstartdate']);
        $employee->PositionEndDate = new Carbon($requestData['positionenddate']);

        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function saveContactInformation($employee,$requestData)
    {

        $employee->MobilePhone = $requestData['personalmobilephone'];
        $employee->HomePhone = $requestData['personalhomephone'];
        $employee->REMMail = $requestData['personalemail'];
        $employee->Email = $requestData['kepemail'];


        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }



}
