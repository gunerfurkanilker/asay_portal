<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Library\Bitrix;

class AuthController extends Controller
{
    public function loginPost(Request $request)
    {
        $data["username"] = $request->input("username");
        $data["password"] = $request->input("password");
        if(filter_var( $data["username"], FILTER_VALIDATE_EMAIL))
            $email = $data["username"];
        else
            $email = $data["username"]."@asay.com.tr";

        $error = false;
        $connections = [
            'asay.corp' => [
                'hosts' => ['asay.corp'],
            ],
        ];

        $ad = new \Adldap\Adldap($connections);

        try {
            $provider = $ad->connect("asay.corp", $email, $data["password"]);
            $search = $provider->search();
            $user = UserModel::LdapUserCreate($search,$data["username"]);

            $userdata = [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'active' => $user->active,
                'user_group' => $user->user_group
            ];
            $userdata["token"] = UserModel::createToken($userdata);
        } catch (\Adldap\Auth\BindException $e) {
            $error = "Kullanıcı adı ve şifre hatası";
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
