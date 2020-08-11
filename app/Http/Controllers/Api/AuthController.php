<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        if(filter_var( $data["username"], FILTER_VALIDATE_EMAIL))
            $email = $data["username"];
        else
            $email = $data["username"]."@asay.com.tr";

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
            $user = UserModel::LdapUserCreate($search,$data["username"]);
            $Menus = UserMenuModel::UserMenus($user->user_group);
            $userdata = [
                'user_id' => $user->id,
                'EmployeeID' => $user->EmployeeID,
                'username' => $user->username,
                'email' => $user->email,
                'photo' => "http://portal.asay.com.tr/".$user->photo,
                'full_name' => $user->full_name,
                //'manager' => explode(",",explode("CN=",$user->user_property->manager)[1])[0],
                'active' => $user->active,
                'user_group' => $user->user_group,
                "user_menus" => json_encode($Menus),
            ];
            $userdata["token"] = UserModel::createToken($userdata);
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
        $userCheck = UserModel::tokenControl($data["token"]);
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
