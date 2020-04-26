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
        $requestData = $request->all();
        $employee = EmployeeModel::find($requestData['employeeid']);
       if (!is_null($employee))
       {
            if ($employee->LocationID != null)
                $location = LocationModel::saveLocation($requestData,$employee->LocationID);
            else
                $location = LocationModel::addLocation($requestData,$employee);

            if ($location)
                return response([
                    'status' => true,
                    'message' => $location->Id . " ID No'lu Lokasyon Kaydedildi",
                    'data' =>$location
                ],200);
            else
                return response([
                    'status' => false,
                    'message' => "İşlem Başarısız."
                ],200);
       }
       else
       {
        return response([
            'status' => false,
            'message' => $employeeId. " ID No'lu Çalışan bulunamadı."
        ],200);
       }
    }

    public function getLocation($id){
        $employee = EmployeeModel::find($id);

        if ($employee->LocationID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => LocationModel::find($employee->LocationID)
            ],200);

    }

    public function getLocationInformationFields(){
        $fields = LocationModel::getLocationFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }
}
