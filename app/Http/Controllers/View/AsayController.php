<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;

class AsayController extends Controller
{

    function __construct()
    {
        $this->middleware(function ($request,$next) {
            $token = "";
            if(isset($request->session()->get("user")->token))
                $token = $request->session()->get("user")->token;
            if (!isset($token) || empty($token)) {
                return redirect("login");
            }
            $client = new \GuzzleHttp\Client();
            $rest = json_decode($client->post($this->api_url."auth/loginCheck", ["form_params"=>["token"=>$token]])->getBody());
            if($rest->status==false)
                return redirect("login");

            return $next($request);
        });
    }
}
