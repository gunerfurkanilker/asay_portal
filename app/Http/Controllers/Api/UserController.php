<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function getUser(Request $request)
    {
        $user = UserModel::find($request->input("user_id"));
        return response([
            'status' => true,
            'data' => $user
        ], 200);
    }
}
