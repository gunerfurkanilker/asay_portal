<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\CarModel;
use Illuminate\Http\Request;

class CarController extends  ApiController
{

    public function getCars(Request $request)
    {
        $cars = CarModel::where(['Active' => 1,'ProjectID' => $request->ProjectID])->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $cars
        ],200);
    }


}
