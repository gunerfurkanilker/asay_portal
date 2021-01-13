<?php

namespace App\Model;


use App\Library\Asay;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeeModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Employee";
    protected $guarded = [];
    const CREATED_AT = 'CreateDate';
    const UPDATED_AT = 'LastUpdateDate';

    protected $appends = [
        "ContractType",
        'IDCard',
        'Domain',
        'EmployeePosition',
        'AccessTypes',
        'EmployeeGroup',
        'MobilePhone',
        'HomePhone',
        'REMMail',
        'Email',
        'BloodTypeID'
    ];

    public static function getLastStaffID(){
        $ID_NO = 8011925;
        while(true)
        {
            $count = EmployeeModel::where(['StaffID' => $ID_NO])->count();
            if ($count > 0)
                $ID_NO++;
            else
                break;
        }

        return $ID_NO;
    }

    public static function addEmployee($request)
    {
        $employee = new EmployeeModel();
        $request->JobEmail = preg_replace("/\s+/", "", $request->JobEmail);
        $employee->StaffID              = $request->staffId ? $request->staffId : self::getLastStaffID() ;
        $employee->FirstName            = $request->FirstName;
        $employee->UsageName            = $request->UsageName;
        $employee->LastName             = $request->LastName;
        $employee->DomainID             = $request->DomainID;
        $employee->JobEmail             = isset($request->JobEmail) ? $request->JobEmail : null;
        $employee->JobMobilePhone       = isset($request->JobMobilePhone)  ? $request->JobMobilePhone : null;
        $employee->InterPhone           = isset($request->InterPhone)  ? $request->InterPhone : null;


        try
        {
            $employee->save();
            $loggedUser = DB::table("Employee")->find($request->Employee);
            LogsModel::setLog($request->Employee,$employee->Id,15,34,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adında bir çalışan oluşturdu","","","","","");

        }catch (QueryException $queryException)
        {
            $errorCode = $queryException->errorInfo[1];
            if ($errorCode == 1062)// Duplicate Entry Code JobEmail İçin
            {
                $i=1;
                while (true)
                {
                    try
                    {
                        $mailPreSection = explode("@",$request->JobEmail)[0];
                        $mailPostSection = explode("@",$request->JobEmail)[1];

                        $mailPreSection = $mailPreSection . $i;
                        $mailFull = $mailPreSection .'@'. $mailPostSection;
                        $employee->JobEmail = $mailFull;
                        $employee->save();
                        break;

                    }catch (QueryException $queryException1)
                    {
                        $i++;
                    }
                }

            }

        }

        //Erişim Tiplerini Belirliyoruz.
        self::saveEmployeeAccessType($request->AccessTypes,$employee->Id);

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
        $data['workingschedulefield']   = WorkingScheduleModel::all();
        $data['contractypefield']       = ContractTypeModel::all();
        $data['accesstypefield']        = UserGroupModel::all();
        $data['domainfield']            = DomainModel::where('active', 1)->get();

        return $data;

    }



    public static function saveGeneralInformations($employee,$request)
    {


        $employee->StaffID              = $request->staffId;
        $employee->FirstName            = $request->firstname;
        $employee->UsageName            = $request->usagename;
        $employee->LastName             = $request->lastname;
        $employee->DomainID             = $request->domain;
        $employee->JobEmail             = $request->jobemail;
        $employee->JobMobilePhone       = $request->jobphone;
        $employee->InterPhone           = $request->internalphone;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $dirtyFields = $employee->getDirty();
        $dirtyFieldsString = "";
        $dirtyFieldsArray = [];
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $employee->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,35,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın genel bilgilerini düzenledi","","","","","");
            }
        }


        try
        {
            $employee->save();
            $employee = $employee->fresh();

        }catch (QueryException $queryException)
        {
            $errorCode = $queryException->errorInfo[1];
            if ($errorCode == 1062)// Duplicate Entry Code JobEmail İçin
            {
                $i=1;
                while (true)
                {
                    try
                    {
                        $mailPreSection = explode("@",$request->jobemail)[0];
                        $mailPostSection = explode("@",$request->jobemail)[1];

                        $mailPreSection = $mailPreSection . $i;
                        $mailFull = $mailPreSection .'@'. $mailPostSection;
                        $employee->JobEmail = $mailFull;
                        $employee->save();
                        break;

                    }catch (QueryException $queryException1)
                    {
                        $i++;
                    }
                }

            }

        }


        self::saveEmployeeAccessType($request->accesstypes,$employee->Id);

        if ($request->hasFile('ProfilePicture')) {
            $file = file_get_contents($request->ProfilePicture->path());
            $guzzleParams = [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'IDCardPhoto_' . $employee->Id . '.' . $request->ProfilePicture->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'id_card'
                    ],
                    [
                        'name' => 'token',
                        'contents' => $request->token
                    ]
                ]
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $employee->Photo = $responseBody->data;
                $employee->save();
            }
        }


        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function saveEmployeeAccessType($accessTypeIDs,$employeeID)
    {
        $accessTypeIDs = !is_array($accessTypeIDs) ? explode(",","".$accessTypeIDs) : $accessTypeIDs;
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

    public static function saveOtherInformations($employee,$request)
    {
        $employee->ContractTypeID    = $request->contracttypeid;
        $employee->StartDate            = new Carbon($request->jobbegindate);
        $employee->ContractFinishDate   = isset($request->contractfinishdate) ? new Carbon($request->contractfinishdate) : null;
        $employee->WorkingScheduleID    = $request->workingscheduleid;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $dirtyFields = $employee->getDirty();
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $employee->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,38,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın sözleşme bilgisini düzenledi","","","",$field,"");
            }
        }

        if ($employee->save())
            return $employee->fresh();
        else
            return false;
    }

    public static function saveContactInformation($employee,$request)
    {

        $employee->MobilePhone = $request->personalmobilephone;
        $employee->HomePhone = $request->personalhomephone;
        $employee->Email = $request->personalemail;
        $employee->REMMail = $request->kepemail;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $dirtyFields = $employee->getDirty();
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $employee->getOriginal($field);
            if ($olddata != $newdata) {
                LogsModel::setLog($request->Employee,$employee->Id,15,45,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın iletişim bilgisini güncelledi","","","",$field,"");
            }
        }


        if ($employee->save())
            return $employee->fresh();
        else
            return false;

    }

    public static function LdapUserLogin($search,$email,$ldap)
    {
        $userDetail = $search->in($ldap->base_dn)->findBy('mail', $email);

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
            if ($userGroup->active == 1)
            {
                $group = UserGroupModel::find($userGroup->group_id);
                $groups[$userGroup->group_id] = $group->name;
            }
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

    public function setMobilePhoneAttribute($value)
    {
        $this->attributes['MobilePhone'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getMobilePhoneAttribute($value)
    {
        try {
            return $this->attributes['MobilePhone'] !== null || $this->attributes['MobilePhone'] != '' ? Crypt::decryptString($this->attributes['MobilePhone']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setHomePhoneAttribute($value)
    {
        $this->attributes['HomePhone'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getHomePhoneAttribute($value)
    {
        try {
            return $this->attributes['HomePhone'] !== null || $this->attributes['HomePhone'] != '' ? Crypt::decryptString($this->attributes['HomePhone']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setREMMailAttribute($value)
    {
        $this->attributes['REMMail'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getREMMailAttribute($value)
    {
        try {
            return $this->attributes['REMMail'] !== null || $this->attributes['REMMail'] != '' ? Crypt::decryptString($this->attributes['REMMail']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['Email'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getEmailAttribute($value)
    {
        try {
            return $this->attributes['Email'] !== null || $this->attributes['Email'] != '' ? Crypt::decryptString($this->attributes['Email']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setBloodTypeIDAttribute($value)
    {
        $this->attributes['BloodTypeID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getBloodTypeIDAttribute($value)
    {
        try {
            return $this->attributes['BloodTypeID'] !== null || $this->attributes['BloodTypeID'] != '' ? (int) Crypt::decryptString($this->attributes['BloodTypeID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }


}
