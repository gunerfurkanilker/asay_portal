<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\Employee;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;


class EmployeeController extends ApiController
{

    public function allEmployees()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::all()
        ],200);
    }

    public function getGeneralInformationsOfEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
                'generalInfoFields' => EmployeeModel::getGeneralInformationsFields(),
                'companyInfoFields' => EmployeeModel::getCompanyInformationsFields()
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ],200);

    }

    public function saveGeneralInformations(Request $request,$id)
    {
        $requestData = $request->all();
        $employee = EmployeeModel::where('Id',$id)->first();

        $freshData = EmployeeModel::saveGeneralInformations($employee,$requestData);

        if ($freshData)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ],200);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ],200);
    }


    public function saveJobPosition(Request $request,$id)
    {

        $requestData = $request->all();
        $employee = EmployeeModel::where('Id',$id)->first();

        $freshData = EmployeeModel::saveJobPosition($employee,$requestData);

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

    public function deletePosition($id)
    {

    }

    public function saveContactInformation(Request $request,$id)
    {

        $requestData = $request->all();
        $employee = EmployeeModel::where('Id',$id)->first();

        $freshData = EmployeeModel::saveContactInformation($employee,$requestData);

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
