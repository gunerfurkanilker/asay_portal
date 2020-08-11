<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserModel;
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

        $requestArray = $request->all();

        $user = UserModel::find($request->userId);
        $data = [];

        $projectsRelationsOfUser = UserProjectsModel::where('EmployeeID', $user->EmployeeID)->get();
        $projectList = [];

        if (count($projectsRelationsOfUser) < 1) {
            return response([
                'status' => false,
                'message' => 'Kullanıcı herhangi bir projede görev almıyor.'
            ], 200);
        }

        foreach ($projectsRelationsOfUser as $project) {

            $prj = ProjectsModel::find($project->project_id);

            if ($prj) {
                array_push($projectList, $prj);
            }


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
            $categories = ProjectCategoriesModel::where('project_id', $project_id)->get();
        else
            $categories = ProjectCategoriesModel::where('project_id', $project_id)->where('expense_type',$expenseType)->get();
        // Expense Type geldi ise ilgili expense Type'ın alt kategorilerini seçiyorum.
        $data['categories'] = $categories;

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $data
        ], 200);

    }

}
