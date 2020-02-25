<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserTokensModel;
use Illuminate\Http\Request;
use App\Model\UserModel;

class ApiController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request,$next) {
            $token = $request->input("token");

            if (!isset($token) || empty($token)) {
                $token = $request->input("token");
            }

            if (!isset($token) || empty($token) || !UserModel::tokenControl($token)) {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz işlem",
                    'error' => "unauthorized",
                ], 200);
            }

            $userId = UserTokensModel::where(["user_token"=>$token])->first()->user_id;
            $request->userId = $userId;
            return $next($request);
        });
    }
}
