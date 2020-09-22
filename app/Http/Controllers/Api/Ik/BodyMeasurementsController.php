<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BodyMeasurementModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class BodyMeasurementsController extends ApiController
{
    public function saveBodyMeasurements(Request $request)
    {

        $bodyMeasurements = BodyMeasurementModel::saveBodyMeasurements($request);

        if ($bodyMeasurements)
            return response([
                'status' => true,
                'message' => "İşlem Başarılı",
                'data' => $bodyMeasurements
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız."
            ], 200);

    }

    public function getBodyMeasurements($id)
    {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => BodyMeasurementModel::where("EmployeeID",$id)->get()
            ], 200);
    }

    public function getBodyMeasurementsFields()
    {
        $fields = BodyMeasurementModel::getBodyMeasurementFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }
}
