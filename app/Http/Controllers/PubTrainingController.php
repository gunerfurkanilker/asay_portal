<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PubTrainingController extends Controller
{
    //


     public function getTrainingByID($id){

                 $employeeTrainings = EmployeeTrainingModel::where(["EmployeeID" => $id]);
                      return response([
                             'status' => true,
                             'data' => $employeeTrainings
                         ],200);

        }

    public function resultQr1(Request $request){
        $employee=QrModel::where(function($query) use ($request){
            $query->where('number',$request->code)->where('updated_at','>',Carbon::now()->subMinutes(5));
        })->firstOrFail();
        $employee=EmployeeModel::findOrFail($employee->EmployeeID);
        return response([
            'test'=>$employee->employee
        ]);
    }
}
