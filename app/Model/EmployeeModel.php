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
        'EmployeeBank',
        'Domain'
    ];

    public static function addEmployee($requestData)
    {
        $employee = new EmployeeModel();

        $employee->StaffID              = isset($requestData['staffId']) ? $requestData['staffId'] : null ;
        $employee->FirstName            = $requestData['FirstName'];
        $employee->UsageName            = $requestData['UsageName'];
        $employee->LastName             = $requestData['LastName'];
        $employee->DomainID             = $requestData['DomainID'];
        $employee->JobEmail             = $requestData['JobEmail'];
        $employee->JobMobilePhone       = isset($requestData['JobMobilePhone'])  ? $requestData['JobMobilePhone'] : null;
        $employee->InterPhone           = isset($requestData['InterPhone'])  ? $requestData['InterPhone'] : null;
        $employee->ContractTypeID       = $requestData['ContractTypeID'];

        $employee->save();
        $employee = $employee->fresh();

        //Erişim Tiplerini Belirliyoruz.
        self::saveEmployeeAccessType($requestData['AccessTypes'],$employee->Id);

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

    public static function getGeneralInformationsFields($employeeId)
    {
        $data = [];
        $data['accesstypefield'] = UserGroupModel::all();
        $data['contractypefield'] = ContractTypeModel::where('Active',1)->get();
        $data['workingschedulefield'] = WorkingScheduleModel::all();
        $data['domainfield'] = DomainModel::where('active', 1)->get();

        return $data;

    }



    public static function saveGeneralInformations($employee,$requestData)
    {

        $employee->StaffID              = $requestData['staffId'];
        $employee->FirstName            = $requestData['firstname'];
        $employee->UsageName            = $requestData['usagename'];
        $employee->LastName             = $requestData['lastname'];
        $employee->DomainID             = $requestData['domain'];
        $employee->JobEmail             = $requestData['jobemail'];
        $employee->JobMobilePhone       = $requestData['jobphone'];
        $employee->InterPhone           = $requestData['internalphone'];
        $employee->ContractTypeID       = $requestData['contracttypeid'];

        self::saveEmployeeAccessType($requestData['accesstypes'],$employee->Id);

        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function saveEmployeeAccessType($accessTypeIDs,$employeeID)
    {

        $currentAccessTypes = EmployeeHasGroupModel::where('EmployeeID',$employeeID)->where('active',1)->get();

        $currentAccessTypeIDs  = [];

        foreach ($currentAccessTypes as $currentAccessType)
        {
            array_push($currentAccessTypeIDs,$currentAccessType->group_id);
        }



        foreach ($currentAccessTypeIDs as $currentAccessTypeID)
        {

            if (!in_array($currentAccessTypeID,$accessTypeIDs))
            {
                $obj = EmployeeHasGroupModel::where('EmployeeID',$employeeID)->where('group_id',$currentAccessTypeID)
                ->update(['active' => 0]);
            }
        }
        foreach ($accessTypeIDs as $accessTypeID)
        {

            $accessType = EmployeeHasGroupModel::where('EmployeeID',$employeeID)->where('group_id',$accessTypeID);
            if(!$accessType->first())
            {
                $newAccessType = new EmployeeHasGroupModel();

                $newAccessType->EmployeeID = $employeeID;
                $newAccessType->group_id = $accessTypeID;
                $newAccessType->active = 1;
                $newAccessType->save();
            }
            else{
                $accessType->update(['active' => 1]);
            }


        }

    }

    public static function getAccessTypes($id)
    {
        $accessTypeIDs = [];
        $accessTypeObjects = EmployeeHasGroupModel::select('group_id')->where('EmployeeID',$id)->where('active',1)->get();
        foreach ($accessTypeObjects as $val)
        {
            array_push($accessTypeIDs,$val->group_id);
        }
        return $accessTypeIDs;
    }

    public static function saveOtherInformations($employee,$requestData)
    {
        $employee->ContractTypeID    = $requestData['contracttypeid'];
        $employee->StartDate            = new Carbon($requestData['jobbegindate']);
        $employee->ContractFinishDate   = isset($requestData['contractfinishdate']) ? new Carbon($requestData['contractfinishdate']) : null;
        $employee->WorkingScheduleID    = $requestData['workingscheduleid'];

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

    public function getDomainAttribute()
    {
        $domain = $this->hasOne(DomainModel::class,"id","DomainID");
        if ($domain)
        {
            return $domain->first();
        }
        else
        {
            return "";
        }
    }


}
