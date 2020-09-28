<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BloodTypeModel;
use App\Model\EmergencyFieldModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class EmergencyFieldController extends ApiController
{
    public function saveEmergencyField(Request $request)
    {
        $status = EmergencyFieldModel::saveEmergencyField($request);

        if ($status)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'request' => $request->all()
            ], 200);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız',
            ], 200);

    }

    public function getEmergencyInformations($id)
    {
        $employee = EmployeeModel::find($id);

        $data = [];

        $data['FirstPerson']    = EmergencyFieldModel::where(['EmployeeID' => $id, 'Priority' => 1])->first();
        $data['SecondPerson']   = EmergencyFieldModel::where(['EmployeeID' => $id, 'Priority' => 0])->first();
        $data['BloodTypeID']    = EmergencyFieldModel::where(['EmployeeID' => $id])->first() ? EmergencyFieldModel::where(['EmployeeID' => $id])->first()->BloodTypeID : null;

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $data
            ], 200);

    }

    public function getEmergencyInformationFields()
    {
        $fields = EmergencyFieldModel::getEmergencyFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }
}
