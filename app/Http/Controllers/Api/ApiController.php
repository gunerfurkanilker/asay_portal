<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            return $next($request);
        });
    }
}
