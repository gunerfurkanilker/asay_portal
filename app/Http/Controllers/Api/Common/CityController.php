<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\CityModel;
use Illuminate\Http\Request;

class CityController extends ApiController
{

    public function getCities(Request $request){


        $citiesOfTurkey = $request->RegionID ? CityModel::where(['Active' => 1, 'RegionID' => $request->RegionID])->get() : CityModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $citiesOfTurkey
        ],200);
    }

    public function getDistrictsOfCity(Request $request){

        $request_data = $request->all();
        $cities = CityModel::getDistrictsOfCity($request_data);
        if($cities)
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $cities
            ],200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => 'Seçili Şehre Ait İlçe Bulunamadı.'
            ],200);
        }



    }

}
