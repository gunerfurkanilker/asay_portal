<?php

namespace App\Model;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Employee";
    protected $guarded = [];
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
        "Education",
        "Location",
        "Title",
        "Manager",
        "WorkingType",
        "Payment",
        "DrivingLicense",
        "AGI",
        "EmergencyField",
        "BodyMeasurements",
        'IDCard',
        'SocialSecurityInformation',
        'EmployeeBank'
    ];

    public static function addEmployee($request_data)
    {
        unset($request_data['token']);
        $employee = self::create($request_data);
        $employee->save();
        return $employee->fresh();
    }

    public static function deleteEmployee($id)
    {
        $employee = self::find($id);
        try
        {
            $employee->Active == 0 ? $employee->Active = 1 : $employee->Active = 0  ;
            $employee->save();
            return true;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function getGeneralInformationsFields()
    {
        $data = [];
        $data['accesstypefield'] = AccessTypeModel::all();
        $data['contractypefield'] = ContractTypeModel::all();
        $data['workingschedulefield'] = WorkingScheduleModel::all();

        return $data;

    }



    public static function saveGeneralInformations($employee,$requestData)
    {
        $employee->FirstName = $requestData['firstname'];
        $employee->UsageName = $requestData['usagename'];
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



    public static function saveContactInformation($employee,$requestData)
    {

        $employee->MobilePhone = $requestData['personalmobilephone'];
        $employee->HomePhone = $requestData['personalhomephone'];
        $employee->Email = $requestData['personalemail'];
        $employee->REMMail = $requestData['kepemail'];


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

        $payment = $this->hasOne(PaymentModel::class,"Id","PaymentID");
        if ($payment->select('*')->first() != null)
        {
            return PaymentModel::find($payment->select('*')->first()->Id);
        }
        else
        {
            return "";
        }
    }

    public function getEducationAttribute()
    {

        $education = $this->hasOne(EducationModel::class,"Id","EducationID");
        if ($education)
        {
            return $education->first();
        }
        else
        {
            return "";
        }
    }

    public function getDrivingLicenseAttribute()
    {

        $drivingLicense = $this->hasOne(DrivingLicenseModel::class,"Id","DrivingLicenceID");
        if ($drivingLicense)
        {
            return $drivingLicense->first();
        }
        else
        {
            return "";
        }
    }

    public function getAGIAttribute()
    {

        $agi = $this->hasOne(AgiModel::class,"Id","AGIID");
        if ($agi)
        {
            return $agi->first();
        }
        else
        {
            return "";
        }
    }

    public function getEmergencyFieldAttribute()
    {

        $emergencyField = $this->hasOne(EmergencyFieldModel::class,"Id","EmergencyFieldID");
        if ($emergencyField)
        {
            return $emergencyField->first();
        }
        else
        {
            return "";
        }
    }

    public function getBodyMeasurementsAttribute()
    {

        $bodyMeasurements = $this->hasOne(BodyMeasurementModel::class,"Id","BodyMeasurementID");
        if ($bodyMeasurements)
        {
            return $bodyMeasurements->first();
        }
        else
        {
            return "";
        }
    }

    public function getIDCardAttribute()
    {

        $idCard = $this->hasOne(IdCardModel::class,"Id","IDCardID");
        if ($idCard)
        {
            return $idCard->first();
        }
        else
        {
            return "";
        }
    }

    public function getSocialSecurityInformationAttribute()
    {
        $socialSecurityInformation = $this->hasOne(SocialSecurityInformationModel::class,"Id","IDCardID");
        if ($socialSecurityInformation)
        {
            return $socialSecurityInformation->first();
        }
        else
        {
            return "";
        }
    }

    public function getEmployeeBankAttribute()
    {
        $employeeBank = $this->hasOne(EmployeeBankModel::class,"Id","EmployeeBankID");
        if ($employeeBank)
        {
            return $employeeBank->first();
        }
        else
        {
            return "";
        }
    }

    public function getLocationAttribute()
    {
        $location = $this->hasOne(LocationModel::class,"Id","LocationID");
        if ($location)
        {
            return $location->first();
        }
        else
        {
            return "";
        }
    }


}
