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
        'Domain',
        'EmployeePosition',
        'AccessTypes',
        'EmployeeGroup'
    ];

    public static function addEmployee($requestData)
    {
        $employee = new EmployeeModel();

        $employee->StaffID              = isset($requestData['staffId']) ? $requestData['staffId'] : null ;
        $employee->FirstName            = $requestData['FirstName'];
        $employee->UsageName            = $requestData['UsageName'];
        $employee->LastName             = $requestData['LastName'];
        $employee->DomainID             = $requestData['DomainID'];
        $employee->JobEmail             = isset($requestData['JobEmail']) ? $requestData['JobEmail'] : null;
        $employee->JobMobilePhone       = isset($requestData['JobMobilePhone'])  ? $requestData['JobMobilePhone'] : null;
        $employee->InterPhone           = isset($requestData['InterPhone'])  ? $requestData['InterPhone'] : null;
        $employee->ContractTypeID       = $requestData['ContractTypeID'];

        $employee->save();
        $employee = $employee->fresh();

        if (isset($requestData['activedirectoryuserid']) && $requestData['activedirectoryuserid'] != null && $requestData['activedirectoryuserid'] != "")
        {
            $employeeUser = UserModel::find($requestData['activedirectoryuserid']);
            $employeeUser->EmployeeID = $employee->Id;
            $employeeUser->save();
        }

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
        $data['accesstypefield']        = UserGroupModel::all();
        $data['contractypefield']       = ContractTypeModel::where('Active',1)->get();
        $data['workingschedulefield']   = WorkingScheduleModel::all();
        $data['domainfield']            = DomainModel::where('active', 1)->get();
        $data['activedirectoryusers']   = UserModel::where(['active' => 1])->get();

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

    public static function LdapUserLogin($search,$username)
    {
        $userDetail = $search->in('DC=asay,DC=corp')->findBy('samaccountname', $username);

        if($userDetail->useraccountcontrol[0]==66048 || $userDetail->useraccountcontrol[0]==66080  || $userDetail->useraccountcontrol[0]==512)
            return true;
        else
            return false;
    }


    public static function createToken($data)
    {
        $tokenSearch = UserTokensModel::where("EmployeeID", $data["EmployeeID"]);
        $Employee = self::find($data["EmployeeID"]);
        $token = "";
        if($Employee->multi_session==1)
        {
            if($tokenSearch->count()>0)
            {
                $tokenDetail = $tokenSearch->first();
                if (self::tokenControl($tokenDetail->user_token)) {
                    $token = $tokenDetail->user_token;
                }
            }
        }
        if($token=="")
        {
            $token = md5(bin2hex(openssl_random_pseudo_bytes(16)) . $data["email"]);
        }

        if ($tokenSearch->first()) {
            $tokenSearch->update(["user_token" => $token]);
        } else {
            $userToken = new UserTokensModel();
            $userToken->EmployeeID = $data["EmployeeID"];
            $userToken->user_token = $token;
            $userToken->save();
        }

        return $token;
    }


    public static function tokenControl($token)
    {
        global $asayData;
        $tokenSearch = UserTokensModel::where("user_token", $token)->first();
        if (!$tokenSearch) {
            return false;
        } else {

            $date1 = strtotime($tokenSearch->updated_at);
            $date2 = strtotime(date("Y-m-d H:i:s"));
            $asayData["user_id"] = $tokenSearch->user_id;
            $hours = abs($date2-$date1)/(60*60);
            if ((int)$hours > 4) {
                return false;
            }
        }

        self::updateToken($token);
        return true;
    }

    public static function updateToken($token)
    {
        $now = date("Y-m-d H:i:s");
        $tokenSearch = UserTokensModel::where("user_token", $token);
        $tokenSearch->update(["updated_at" => $now]);
    }

    public function getEmployeeGroupAttribute()
    {
        $groups = [];
        $userGroups = $this->hasMany(EmployeeHasGroupModel::class, "EmployeeID", "Id")->get();
        foreach ($userGroups as $userGroup) {
            $group = UserGroupModel::find($userGroup->group_id);
            $groups[$userGroup->group_id] = $group->name;
        }
        return $groups;
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

    public function getEmployeePositionAttribute()
    {
        $position = $this->hasOne(EmployeePositionModel::class,"EmployeeID","Id");
        if ($position)
        {
            return $position->where(['Active' => 2])->first();
        }
        else
        {
            return "";
        }
    }

    public function getAccessTypesAttribute()
    {
        $accessTypes = $this->hasMany(EmployeeHasGroupModel::class,"EmployeeID","Id")->where('active','=',1);
        if ($accessTypes)
        {
            $accessTypeIDs = [];
            foreach ($accessTypes->get() as $accessType)
            {
                array_push($accessTypeIDs,$accessType->group_id);
            }
            return $accessTypeIDs;
        }
        else
        {
            return "";
        }
    }


}
