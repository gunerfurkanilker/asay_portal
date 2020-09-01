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
            $token = $request->token;

            if (!isset($token) || empty($token)) {
                $token = $request->token;
            }

            if (!isset($token) || empty($token) || !UserModel::tokenControl($token)) {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz işlem",
                    'error' => "unauthorized"
                ], 200);
            }

            $user = UserTokensModel::select("user.*")
                ->leftJoin("user","user.id","=","user_tokens.user_id")
                ->where(["user_token"=>$token])->first();
            $request->userId        = $user->id;
            //$request->EmployeeID    = $user->EmployeeID;
            return $next($request);
        });
    }
}
