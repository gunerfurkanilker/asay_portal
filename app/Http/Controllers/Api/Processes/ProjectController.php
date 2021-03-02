<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\CarModel;
use App\Model\EmployeePositionModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserProjectsModel;
use Illuminate\Http\Request;

class ProjectController extends ApiController
{

    public function getProject(Request $request)
    {
        $project = ProjectsModel::find($request->project_id);

        if ($project != null)
            return response([
                'status' => true,
                'data' => $project
            ], 200);
        else
            return response([
                'status' => false,
                'message' => 'Proje Bulunamadı.'
            ], 200);

    }

    public function projectListOfUser(Request $request)
    {
        $data = [];

        $employeeOrganizationID = EmployeePositionModel::where(['EmployeeID' => $request->Employee, 'Active' => 2])->first()->OrganizationID;

        $projectList = [];
        if ($employeeOrganizationID == 4)//MSMARMARA
        {
            $project = ProjectsModel::find(1);//MS_PROJESİ
            array_push($projectList,$project);//MSPROJESİ
        }
        else
        {
            $projectList = ProjectsModel::whereNotIn("id",[1,3,4])->get();
        }

        $data['userProjectList'] = $projectList;

        return response([
            'status' => true,
            'data' => $data
        ], 200);

    }

    public function categoryListOfProject(Request $request)
    {

        $data = [];

        $project_id = $request->input('project_id');
        $expenseType = $request->input('expense_type') ? $request->input('expense_type') : null;

        if (!$expenseType)
            $categories = ProjectCategoriesModel::where(['project_id' => $project_id,'active' => 1])->get();
        else
            $categories = ProjectCategoriesModel::where('project_id', $project_id)->where('expense_type',$expenseType)->where("active" , 1)->get();
        // Expense Type geldi ise ilgili expense Type'ın alt kategorilerini seçiyorum.
        $data['categories'] = $categories;

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $data
        ], 200);

    }

    public function getProjectCars(Request $request){

        if (is_null($request->projectId) || $request->projectId == "")
            return response([
                'status' => true,
                'message' => 'Proje Id boş olamaz'
            ],200);

        $carList = CarModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $carList
        ],200);

    }

}
