<?php

namespace App\Model;


use App\Library\Asay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

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
        'EmployeeGroup'
    ];

    public static function addEmployee($request)
    {
        $employee = new EmployeeModel();

        $employee->StaffID              = isset($request->staffId) ? $request->staffId : null ;
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
            $employee = $employee->fresh();

            $position = new EmployeePositionModel();

            $position->EmployeeID       = $employee->Id;
            $position->Active           = 2;
            $position->CompanyID        = $request->CompanyID;
            $position->OrganizationID   = $request->OrganizationID;
            $position->save();

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
        $data['Companies']              = CompanyModel::where(['Active' => 1])->get();
        $data['Organizations']          = OrganizationModel::where(['Active' => 1])->get();
        $data['accesstypefield']        = UserGroupModel::all();
        $data['contractypefield']       = ContractTypeModel::where('Active',1)->get();
        $data['workingschedulefield']   = WorkingScheduleModel::all();
        $data['domainfield']            = DomainModel::where('active', 1)->get();

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

    public static function LdapUserLogin($search,$username,$ldap)
    {
        $userDetail = $search->in($ldap->base_dn)->findBy('samaccountname', $username);

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
        $position = $this->hasMany(EmployeePositionModel::class,"EmployeeID","Id");
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
