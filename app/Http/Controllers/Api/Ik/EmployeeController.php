<?php


namespace App\Http\Controllers\Api\Ik\Employee;


use App\Http\Controllers\Api\ApiController;
use App\Model\Employee;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;


class EmployeeController extends ApiController
{


    public function addEmployee(Request $request)
    {
        $incomingData = $request->all();
    }

    public function allEmployees()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::all()
        ],200);
    }


    public function getEmployeeById($id)
    {

    }

    public function deleteEmployee($id)
    {

    }

}
