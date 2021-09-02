<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\DomainModel;
use App\Model\EmployeeLogsModel;
use App\Model\EmployeeModel;
use App\Model\LdapModel;
use App\Model\LogsModel;
use App\Model\ParametersModel;
use App\Model\UserMenuModel;
use PHPUnit\Framework\MockObject\Rule\Parameters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function loginPost(Request $request)
    {
        $data["username"]   = $request->username;
        $data["password"]   = $request->password;
        $data["domain"]     = $request->domain!==null ? $request->domain : "ms.asay.com.tr";
        if($data["username"]=="" || $data["password"]=="")
        {
            return response([
                'status' => false,
                'message' => "Kullanıcı adı ve şifre Hatası"
            ], 200);
        }
        $ldap = LdapModel::where(["name"=>$data["domain"]])->first();

        if(filter_var( $data["username"], FILTER_VALIDATE_EMAIL))
            $email = $data["username"];
        else
            $email = $data["username"]."@".$ldap->name;

        $employeeQ = EmployeeModel::where(["JobEmail"=>$email,"Active"=>1]);
        if($employeeQ->count()==0)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem",
                'data' => $email
            ], 200);
        }

        $employee = $employeeQ->first();

        $error = false;
        $connections = [
            'ldap' => [
                'hosts' => [$ldap->domain],
            ],
        ];

        $ad = new \Adldap\Adldap($connections);

        try {
            $provider = $ad->connect("ldap", $data["username"]."@".$ldap->domain, $data["password"]);
            /*$search = $provider->search();
            //$user = UserModel::LdapUserCreate($search,$data["username"]);
            $userLogin = EmployeeModel::LdapUserLogin($search,$employee->JobEmail,$ldap);
            if(!$userLogin)
            {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz İşlem"
                ], 200);
            }*/


            $Menus = UserMenuModel::UserMenus($employee->EmployeeGroup);

            $userdata = [
                'EmployeeID' => $employee->Id,
                'email' => $employee->JobEmail,
                'photo' => "http://".\request()->getHttpHost()."/".$employee->Photo,
                'full_name' => $employee->UsageName." ".$employee->LastName,
                //'manager' => explode(",",explode("CN=",$user->user_property->manager)[1])[0],
                'active' => $employee->Active,
                'user_group' => $employee->EmployeeGroup,
                "user_menus" => json_encode($Menus),
                'isUnitSupervisor' => $employee->IsUnitSupervisor,
                'isEmployeeManager' => $employee->IsEmployeeManager
            ];
            $userdata["token"] = EmployeeModel::createToken($userdata);
            $firstLogin = EmployeeLogsModel::where(["LogType"=>"LOGIN","EmployeeID"=>$employee->Id,["LogDate",">=",date("Y-m-d")." ".ParametersModel::where(["metaKey"=>"userFirstLoginTime"])->first()->metaValue]])->count();
            $userdata["firstLogin"] = $firstLogin==0 ? true : false;

        } catch (\Adldap\Auth\BindException $e) {
            $error = "Kullanıcı adı ve şifre hatası ".$e->getMessage();
        }
        if ($error) {
            return response([
                'status' => false,
                'message' => $error
            ], 200);
        } else {
            EmployeeLogsModel::setLog($employee->Id,"LOGIN");
            return response([
                'status' => true,
                'data' => [
                    'user' => $userdata
                ]
            ], 200);
        }
    }

    public function logout(Request $request)
    {
        $employee = EmployeeModel::find($request->EmployeeID);
        $res = EmployeeLogsModel::setLog($employee->Id,"LOGOUT");
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $res
        ],200);
    }

    public function loginCheck(Request $request)
    {
        $data["token"] = $request->input("token");
        $userCheck = EmployeeModel::tokenControl($data["token"]);
        if($userCheck){
            return response([
                'status' => true,
            ], 200);
        }
        else
        {
            return response([
                'status' => false,
            ], 200);
        }
    }
}
