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
        ], 200);
    }


    public function getEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }

    public function addEmployee()
    {

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::addEmployee()
        ], 200);
    }

    public function deleteEmployee(Request $request) {
        $request_data['employeeid'] = $request->all();
        $status = EmployeeModel::deleteEmployee($request['employeeid']);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $status
        ]);
    }

    public function getGeneralInformationsOfEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
                'generalInfoFields' => EmployeeModel::getGeneralInformationsFields()
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }

    public function saveGeneralInformations(Request $request, $id)
    {
        $requestData = $request->all();
        $employee = EmployeeModel::where('Id', $id)->first();

        $freshData = EmployeeModel::saveGeneralInformations($employee, $requestData);

        if ($freshData)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ], 200);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ], 200);
    }

    public function getContactInformationsOfEmployee($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }


    public function saveContactInformation(Request $request)
    {

        $requestData = $request->all();
        $employee = EmployeeModel::find($requestData['employeeid']);

        $freshData = EmployeeModel::saveContactInformation($employee, $requestData);

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
