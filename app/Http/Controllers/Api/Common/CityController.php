<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\CityModel;
use Illuminate\Http\Request;

class CityController extends ApiController
{

    public function getDistrictsOfCity(Request $request){

        $request_data = $request->all();
        $cities = CityModel::getDistrictsOfCity($request_data);
        if($cities)
        {
            return response([
                'status' => true,
                'message' => 'İşlenm Başarılı',
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
