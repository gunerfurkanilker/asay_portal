<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\CountryModel;
use Illuminate\Http\Request;

class CountryController extends ApiController
{

    public function getCitiesOfCountry(Request $request){

        $request_data = $request->all();
        $cities = CountryModel::getCitiesOfCountry($request_data);
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
                'message' => 'Seçili Ülkeye Ait Şehir Bulunamadı.'
            ],200);
        }



    }
}
