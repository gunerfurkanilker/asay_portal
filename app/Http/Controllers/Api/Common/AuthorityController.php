<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeePositionModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserModel;
use Illuminate\Http\Request;

class AuthorityController extends ApiController
{

    public function loggedUserAuthorizations(Request $request){

        $isEmployeeManager = false;
        $isProjectManager = false;
        $isAccounter = false;
        $isHRPersonel = false;

        $user = UserModel::find($request->userId);

        $employeeManagers = EmployeePositionModel::where(["Active"=>2,"ManagerId"=>$user->EmployeeID]);
        $projects   = ProjectsModel::where(["manager_id"=>$user->EmployeeID]);
        $categories = ProjectCategoriesModel::where(["manager_id"=>$user->EmployeeID]);

        if ($projects->count() > 0)
            $isProjectManager = true;
        if ($categories->count() > 0)
            $isProjectManager = true;
        if( $employeeManagers->count() > 0 )
            $isEmployeeManager = true;

        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID"=>$user->EmployeeID,"group_id"=>12])->count();
        if ($userGroupCount > 0)
            $isAccounter = true;

        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID"=>$user->EmployeeID])->whereIn('group_id',[16,17])->count();
        if ($userGroupCount > 0)
            $isHRPersonel = true;

        $data['isEmployeeManager'] = $isEmployeeManager;
        $data['isProjectManager'] = $isProjectManager;
        $data['isAccounter'] = $isAccounter;
        $data['isHRPersonel'] = $isHRPersonel;


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $data
        ],200);
    }

}
