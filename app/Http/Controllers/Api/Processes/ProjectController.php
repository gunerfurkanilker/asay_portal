<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\ProjectsModel;
use App\Model\UserModel;
use App\Model\UserProjectsModel;
use Illuminate\Http\Request;

class ProjectController extends ApiController
{

    public function projectListOfUser(Request $request) {

        $requestArray = $request->all();

        $user = UserModel::find($request->userId);
        $data = [];

        $projectsRelationsOfUser = UserProjectsModel::where('user_id', $user->id)->get();
        $projectList = [];

        if (count($projectsRelationsOfUser) < 1)
        {
            return response([
                'status' => false,
                'message' => 'Kullanıcı herhangi bir projede görev almıyor.'
            ], 200);
        }

        foreach ($projectsRelationsOfUser as  $project)
        {

            $prj = ProjectsModel::find($project->project_id);
            if($prj)
                array_push($projectList,$prj);


        }

        $data['userProjectList'] = $projectList;

        return response([
            'status' => true,
            'data' => $data
        ], 200);

    }

}
