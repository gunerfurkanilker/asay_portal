<?php


namespace App\Http\Controllers\Api\Ik;


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

    public function saveGeneralInformations(Request $request,$id)
    {
        $requestData = $request->all();
        $employee = EmployeeModel::where('Id',$id)->first();

        $isSuccess = EmployeeModel::saveGeneralInformations($employee,$requestData);


        if ($isSuccess)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ]);
    }

    public function getGeneralInformations($id)
    {
        /*$employeeTableFields = ['FirstName','LastName','Domain','JobEmail','JobMobilePhone',
            'InterPhone','StartDate','ContractFinishDate','MobilePhone','HomePhone','REMMail','Email',
            'PositionStartDate','PositionEndDate','CreateDate','LastUpdateDate'];*/


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::all()
        ],200);

    }

    public function savePosition()
    {

    }

    public function editPosition($id)
    {

    }

    public function deletePosition($id)
    {

    }

    public function saveContactInformations(Request $request)
    {

    }






}
