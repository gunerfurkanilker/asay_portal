<?php

namespace App\Http\Controllers\Api\Processes;

use Illuminate\Http\Request;
use App\Model\EmployeeTrainingModel;
use App\Model\EmployeeModel;
use App\Model\TrainingCompanyModel;
use App\Model\TrainingModel;
use App\Model\TrainingResultModel;
use App\Model\TrainingStatusModel;
use Illuminate\Support\Facades\DB;

class PublicTrainingController extends \App\Http\Controllers\Controller
{

    public function getTrainingByID($id){

             $employeeTrainings = EmployeeTrainingModel::Where(["EmployeeID" => $id])->get();

                  return response([
                         'status' => true,
                         'data' => $employeeTrainings
                     ],200);

    }
}
