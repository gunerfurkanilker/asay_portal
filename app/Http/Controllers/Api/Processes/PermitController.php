<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\PermitModel;
use Illuminate\Http\Request;

class PermitController extends ApiController
{
    public function createPermit(Request $request) {

        $status = PermitModel::createPermit($request->all());
        return response([
            'status' => true,
            'message' => "Kayıt Başarılı",
            'data' => $request
        ], 200);

        if ($status)
            return response([
                'status' => true,
                'message' => "Kayıt Başarılı",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "Kayıt Başarısız",
            ], 200);

    }
}
