<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeeTrainingModel;
use App\Model\TrainingCategoryModel;
use App\Model\TrainingCompanyModel;
use App\Model\TrainingModel;
use App\Model\TrainingResultModel;
use App\Model\TrainingStatusModel;
use App\Model\TrainingTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingController extends ApiController
{
    //

    public function saveTrainingCategory(Request $request){

        $result = TrainingCategoryModel::saveTrainingCategory($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız'
        ],200);

    }

    public function saveTraining(Request $request)
    {

        $result = TrainingModel::saveTraining($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız'
        ],200);

    }

    public function saveEmployeeTraining(Request $request){

        $isEmployeeExists = EmployeeModel::where(['Active' => 1, 'Id' => $request->EmployeeID])->first();

        if(!$isEmployeeExists)
            return response([
                'status' => false,
                'message' => "Eğitim eklemesi yapılması istenen çalışan bulunamadı"
            ],200);

        $result = EmployeeTrainingModel::saveEmployeeTraining($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız'
        ],200);

    }

    public function getEmployeeTrainings(Request $request){

        $filters = $request->filters;
        $employeeID = $request->EmployeeID;
        $employeeTrainings = EmployeeTrainingModel::getTrainings($filters,$employeeID);

        return response([
            'message' => 'İşlem Başarılı',
            'data' => $employeeTrainings
        ],200);

    }

    public function getTrainings(Request $request){


        $trainingsQ = TrainingModel::where(["Active" => 1]);

        if($request->CompanyID)
            $trainingsQ->where("CompanyID",$request->CompanyID);

        $trainings = $trainingsQ->get();

        return response([
            'status' => true,
            'data' => $trainings
        ],200);

    }

    public function getEmployees(Request $request){

        $employees = DB::table("Employee")->where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $employees
        ],200);

    }

    public function getCompanies(Request $request){

        $companies = TrainingCompanyModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $companies
        ],200);

    }

    public function getStatuses(Request $request){

        $statuses = TrainingStatusModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $statuses
        ],200);

    }

    public function getISGResults(Request $request){

        $results = TrainingResultModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $results
        ],200);

    }

    public function getCategories(Request $request){

        $categories = TrainingCategoryModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $categories
        ],200);

    }

    public function getTrainingTypes(Request $request){

        $trainingTypes = TrainingTypeModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $trainingTypes
        ],200);

    }


}
