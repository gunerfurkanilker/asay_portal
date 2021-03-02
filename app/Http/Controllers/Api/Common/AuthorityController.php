<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\ProcessesSettingsModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorityController extends ApiController
{

    public function loggedUserAuthorizations(Request $request){

        $isEmployeeManager          = false;
        $isProjectManager           = false;
        $isAccounter                = false;
        $isExpenseAccounter         = false;
        $isAllowanceAccounter       = false;
        $isHRPersonel               = false;
        $isPermitPersonnelManager   = false;
        $isUnitSupervisor           = false;

        $employeeManagers = EmployeePositionModel::where(["Active"=>2,"ManagerID"=>$request->Employee]);
        $projects   = ProjectsModel::where(["manager_id"=>$request->Employee]);
        $categories = ProjectCategoriesModel::where(["manager_id"=>$request->Employee]);
        $unitSupervisors = EmployeePositionModel::where(["Active"=>2,"UnitSupervisorID"=>$request->Employee]);

        if ($projects->count() > 0)
            $isProjectManager = true;
        if ($categories->count() > 0)
            $isProjectManager = true;
        if( $employeeManagers->count() > 0 )
            $isEmployeeManager = true;
        if( $unitSupervisors->count() > 0 )
            $isUnitSupervisor = true;

        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID"=>$request->Employee,"group_id"=>12, 'active' => 1])->count();
        $processSettingExpenseAccounter = ProcessesSettingsModel::where(['object_type' => 1,'PropertyValue' => $request->Employee,'PropertyCode' => 'Accounter'])->count();
        $processSettingAllowanceAccounter = ProcessesSettingsModel::where(['object_type' => 2,'PropertyValue' => $request->Employee,'PropertyCode' => 'Accounter'])->count();
        if ($userGroupCount > 0)
        {
            $isAccounter = true;
        }
        if ($processSettingExpenseAccounter > 0)
        {
            $isExpenseAccounter = true;
        }
        if ($processSettingAllowanceAccounter > 0)
        {
            $isAllowanceAccounter = true;
        }


        $userGroupCount = EmployeePositionModel::where(["EmployeeID"=>$request->Employee, 'Active' => 2])->whereIn('TitleID',[98,99,100])->count();
        $exceptionalEmployees = [753,754];// Onur Bey ve Bahadır'ın Employee ID'leri istisna durumlar
        if ($userGroupCount > 0 || in_array($request->Employee,$exceptionalEmployees)) {
            $isHRPersonel = true;
        }

        $permitPersonelCount = ProcessesSettingsModel::where(['object_type' => 3, 'PropertyCode' => 'PersonnelSpecialist', 'PropertyValue' => $request->Employee])->count();
        if ($permitPersonelCount > 0)
        {
            $userPosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $request->Employee])->first();
            $processSetting = ProcessesSettingsModel::where(['object_type' => $request->ObjectType,'PropertyCode' => 'PersonnelSpecialist', 'RegionID' => $userPosition->RegionID,'PropertyValue' => $request->Employee])->count();
            if ($processSetting > 0){
                $isPermitPersonnelManager = true;
            }
        }


        $data['isEmployeeManager']          = $isEmployeeManager;
        $data['isProjectManager']           = $isProjectManager;
        $data['isAccounter']                = $isAccounter;
        $data['isHRPersonel']               = $isHRPersonel;
        $data['isExpenseAccounter']         = $isExpenseAccounter;
        $data['isAllowanceAccounter']       = $isAllowanceAccounter;
        $data['isPermitPersonnelManager']   = $isPermitPersonnelManager;
        $data['isUnitSupervisor']           = $isUnitSupervisor;



        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $data,
            'request' => $request->all()
        ],200);
    }

}
