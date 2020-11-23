<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\DepartmentModel;

class DepartmentController extends ApiController
{
    public function getDepartmentsContactUs(){
        $departments = DepartmentModel::where(['Active' => 1])->whereIn("Id",[12,28,11,31])->get();
        foreach ($departments as $department)
        {
            $department->Sym = "aSAY ". $department->Sym;
        }
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $departments
        ],200);
    }
}
