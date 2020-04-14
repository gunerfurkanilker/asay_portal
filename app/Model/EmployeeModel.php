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
        "WorkingSchedule",
        "Company",
        "City",
        "District",
        "Department",
        "Title",
        "Manager",
        "WorkingType",
        "Payment"
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

    public function getCompanyAttribute()
    {

        $company = $this->hasOne(CompanyModel::class,"Id","CompanyID");
        if ($company)
        {
            return $company->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getCityAttribute()
    {

        $city = $this->hasOne(CityModel::class,"Id","CityID");
        if ($city)
        {
            return $city->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getDistrictAttribute()
    {

        $district = $this->hasOne(DistrictModel::class,"Id","DistrictID");
        if ($district)
        {
            return $district->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getDepartmentAttribute()
    {

        $department = $this->hasOne(DepartmentModel::class,"Id","DepartmentID");
        if ($department)
        {
            return $department->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getTitleAttribute()
    {

        $title = $this->hasOne(TitleModel::class,"Id","TitleID");
        if ($title)
        {
            return $title->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getManagerAttribute()
    {

        $manager = $this->hasOne(EmployeeModel::class,"Id","ManagerID");
        if ($manager)
        {
            return $manager->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getWorkingTypeAttribute()
    {

        $workingType = $this->hasOne(WorkingTypeModel::class,"Id","WorkingTypeID");
        if ($workingType)
        {
            return $workingType->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getPaymentAttribute()
    {

        $payment = $this->hasOne(WorkingTypeModel::class,"Id","PaymentID");
        if ($payment)
        {
            return PaymentModel::find($payment->where("Active",1)->first()->Id);
        }
        else
        {
            return "";
        }
    }


}
