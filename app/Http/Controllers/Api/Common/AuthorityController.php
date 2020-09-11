<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeePositionModel;
use App\Model\ProcessesSettingsModel;
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
        $isPermitPersonnelManager= false;

        $employeeManagers = EmployeePositionModel::where(["Active"=>2,"ManagerID"=>$request->Employee]);
        $projects   = ProjectsModel::where(["manager_id"=>$request->Employee]);
        $categories = ProjectCategoriesModel::where(["manager_id"=>$request->Employee]);

        if ($projects->count() > 0)
            $isProjectManager = true;
        if ($categories->count() > 0)
            $isProjectManager = true;
        if( $employeeManagers->count() > 0 )
            $isEmployeeManager = true;

        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID"=>$request->Employee,"group_id"=>12, 'active' => 1])->count();
        if ($userGroupCount > 0)
        {
            $isAccounter = true;
        }


        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID"=>$request->Employee, 'active' => 1])->whereIn('group_id',[16,17])->count();
        if ($userGroupCount > 0) {
            $userPosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $request->Employee])->first();
            $processSetting = ProcessesSettingsModel::where(['object_type' => $request->ObjectType,'PropertyCode' => 'HRManager', 'RegionID' => $userPosition->RegionID,'PropertyValue' => $user->EmployeeID])->count();
            if ($processSetting > 0){
                $isHRPersonel = true;
            }
        }

        $permitPersonelCount = ProcessesSettingsModel::where(['object_type' => 3, 'PropertyCode' => 'PersonnelSpecialist', 'PropertyValue' => $user->EmployeeID])->count();
        if ($permitPersonelCount > 0)
        {
            $userPosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $user->EmployeeID])->first();
            $processSetting = ProcessesSettingsModel::where(['object_type' => $request->ObjectType,'PropertyCode' => 'PersonnelSpecialist', 'RegionID' => $userPosition->RegionID,'PropertyValue' => $user->EmployeeID])->count();
            if ($processSetting > 0){
                $isPermitPersonnelManager = true;
            }
        }


        $data['isEmployeeManager'] = $isEmployeeManager;
        $data['isProjectManager'] = $isProjectManager;
        $data['isAccounter'] = $isAccounter;
        $data['isHRPersonel'] = $isHRPersonel;
        $data['isPermitPersonnelManager'] = $isPermitPersonnelManager;



        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $data,
            'request' => $request->all()
        ],200);
    }

}
