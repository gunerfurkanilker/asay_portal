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
    protected $appends = [
        "AccessType",
        "ContractType",
        "WorkingSchedule"
    ];

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


    public function getAccessTypeAttribute()
    {

        $accessType = $this->hasOne(AccessTypeModel::class,"Id","AccessTypeID");
        if ($accessType)
        {
            return $accessType->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getContractTypeAttribute()
    {

        $contractType = $this->hasOne(ContractTypeModel::class,"Id","ContractTypeID");
        if ($contractType)
        {
            return $contractType->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getWorkingScheduleAttribute()
    {

        $workingSchedule = $this->hasOne(WorkingScheduleModel::class,"Id","WorkingScheduleID");
        if ($workingSchedule)
        {
            return $workingSchedule->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }


}
