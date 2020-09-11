<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\DomainModel;
use App\Model\EmployeeModel;
use App\Model\UserMenuModel;
use App\Model\UserModel;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginPost(Request $request)
    {
        $data["username"] = $request->input("username");
        $data["password"] = $request->input("password");
        if($data["username"]=="" || $data["password"]=="")
        {
            return response([
                'status' => false,
                'message' => "Kullanıcı adı ve şifre Hatası"
            ], 200);
        }
        $domain = DomainModel::find(1);
        if(filter_var( $data["username"], FILTER_VALIDATE_EMAIL))
            $email = $data["username"];
        else
            $email = $data["username"]."@".$domain->domain;

        $employeeQ = EmployeeModel::where(["JobEmail"=>$email,"Active"=>1]);
        if($employeeQ->count()==0)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        $employee = $employeeQ->first();

        $error = false;
        $connections = [
            'asay.corp' => [
                'hosts' => ["asay.corp"],
            ],
        ];

        $ad = new \Adldap\Adldap($connections);

        try {
            $provider = $ad->connect("asay.corp", "ASAY\\".$data["username"], $data["password"]);
            $search = $provider->search();
            //$user = UserModel::LdapUserCreate($search,$data["username"]);
            $userLogin = EmployeeModel::LdapUserLogin($search,$data["username"]);
            if(!$userLogin)
            {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz İşlem"
                ], 200);
            }


            $Menus = UserMenuModel::UserMenus($employee->EmployeeGroup);
            $userdata = [
                'EmployeeID' => $employee->Id,
                'email' => $employee->JobEmail,
                'photo' => "http://portal.asay.com.tr/".$employee->Photo,
                'full_name' => $employee->UsageName." ".$employee->LastName,
                //'manager' => explode(",",explode("CN=",$user->user_property->manager)[1])[0],
                'active' => $employee->Active,
                'user_group' => $employee->EmployeeGroup,
                "user_menus" => json_encode($Menus),
            ];
            $userdata["token"] = EmployeeModel::createToken($userdata);
        } catch (\Adldap\Auth\BindException $e) {
            $error = "Kullanıcı adı ve şifre hatası ".$e->getMessage();
        }
        if ($error) {
            return response([
                'status' => false,
                'message' => $error
            ], 200);
        } else {
            return response([
                'status' => true,
                'data' => [
                    'user' => $userdata
                ]
            ], 200);
        }
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
