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
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
                'companyInfoFields' => EmployeePositionModel::getCompanyInformationsFields()
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ],200);
    }


    public function saveJobPosition(Request $request,$id)
    {

        $requestData = $request->all();
        $employee = EmployeeModel::where('Id',$id)->first();

        $freshData = EmployeePositionModel::saveJobPosition($employee,$requestData);

        if ($freshData)
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
