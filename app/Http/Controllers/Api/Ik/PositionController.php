<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use Illuminate\Http\Request;

class PositionController extends ApiController
{
    public function getJobPositionInformations($id)
    {
        $positions = EmployeePositionModel::where('EmployeeID',$id)->get()->toArray();

        if ($positions != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $positions,
                'positionFields' => EmployeePositionModel::getPositionFields()
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ],200);
    }


    public function editJobPosition(Request $request,$employeeId,$positionId)
    {

        $requestData = $request->all();
        $positionOfEmployee = EmployeePositionModel::where('EmployeeID',$employeeId)->where('Id',$positionId)->first();

        $freshData = EmployeePositionModel::editJobPosition($positionOfEmployee,$requestData);

        if (true)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ]);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ]);

    }
}
