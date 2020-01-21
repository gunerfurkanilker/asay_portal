<?php

namespace App\Http\Controllers;

use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Library\Bitrix;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if($request->session()->get("user"))
        {
            $user = $request->session()->get("user");
            $client = new \GuzzleHttp\Client();
            $sendData["token"] = $user->token;
            $rest = json_decode($client->post($this->api_url."auth/loginCheck", ["form_params"=>$sendData])->getBody());
            if($rest->status==true)
                return redirect()->back();
        }
        $data["menu"] = "login";
        return view("ik.login",$data);
    }
    public function loginPost(Request $request)
    {
        if($request->session()->get("user"))
        {
            $user = $request->session()->get("user");
            $client = new \GuzzleHttp\Client();
            $sendData["token"] = $user->token;
            $rest = json_decode($client->post($this->api_url."auth/loginCheck", ["form_params"=>$sendData])->getBody());
            if($rest->status==true)
                return redirect()->back();
        }
        $data["username"] = $request->input("username");
        $data["password"] = $request->input("password");
        if(filter_var( $data["username"], FILTER_VALIDATE_EMAIL))
            $email = $data["username"];
        else
            $email = $data["username"]."@asay.com.tr";

        $client = new \GuzzleHttp\Client();
        $sendData["username"] = $data["username"];
        $sendData["password"] = $data["password"];
        $rest = json_decode($client->post($this->api_url."auth/login", ["form_params"=>$sendData])->getBody());
        if($rest->status==true)
        {
            $request->session()->put('user', $rest->data->user);
            return redirect()->intended();
        }
        else
        {
            return back()->with("message","error");
        }
    }
    public function logout(Request $request)
    {
        $request->session()->forget('user');
        return redirect('/login');
    }
}
