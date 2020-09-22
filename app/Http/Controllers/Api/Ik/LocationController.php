<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use Illuminate\Http\Request;

class LocationController extends ApiController
{
    public function saveLocation(Request $request)
    {
        $location = LocationModel::saveLocation($request);

        if ($location)
            return response([
                'status' => true,
                'message' =>"İşlem Başarılı",
                'data' => $location
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız.",
                'data' => '' . $location
            ], 200);
    }

    public function getLocation($id)
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => LocationModel::where("EmployeeID", $id)->first()
        ], 200);

    }

    public function getLocationInformationFields()
    {
        $fields = LocationModel::getLocationFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }
}
