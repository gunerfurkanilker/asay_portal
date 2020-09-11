<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\EmployeeModel;
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

            if (!isset($token) || empty($token) || !EmployeeModel::tokenControl($token)) {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz işlem",
                    'error' => "unauthorized"
                ], 200);
            }

            $Employee = UserTokensModel::select("Employee.*")
                ->leftJoin("Employee","Employee.Id","=","user_tokens.EmployeeID")
                ->where(["user_token"=>$token])->first();
            //$request->userId        = $user->id;
            $request->Employee      = $Employee->Id;
            return $next($request);
        });
    }
}
