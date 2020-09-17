<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BodyMeasurementModel;
use App\Model\EmployeeModel;
use App\Model\IdCardModel;
use Illuminate\Http\Request;

class IDCardController extends ApiController
{
    public function saveIDCard(Request $request)
    {
        $status = IdCardModel::saveIDCard($request);


        if ($status)
            return response([
                'status' => true,
                'message' => "İşlem Başarılı",
                'data' => $request->IDCardPhoto->path(),
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız."
            ], 200);

    }

    public function getIDCard($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee->IDCardID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => IdCardModel::find($employee->IDCardID)
            ], 200);

    }

    public function getIDCardFields()
    {
        $fields = IdCardModel::getIDCardFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }

}
