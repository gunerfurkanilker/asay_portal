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

        $user = UserModel::find($requestArray['userId']);

        $projectsOfUser = UserProjectsModel::where('user_id', $user->id)->get();

        return response([
            'status' => true,
            'data' => $projectsOfUser
        ], 200);

    }

}
