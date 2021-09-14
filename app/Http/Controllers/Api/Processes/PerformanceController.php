<?php

namespace App\Http\Controllers\Api\Processes;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;

use Illuminate\Support\Facades\Auth;

use App\Model\PerformanceModel;

use App\Model\OvertimeModel;
use App\Model\ProjectsModel;
use App\Model\PublicHolidayModel;
use App\Model\UserProjectsModel;
use App\Http\Resources\PerformanceResource;
use Carbon\Carbon;
use App\Model\PerformanceWeightModel;

class PerformanceController extends ApiController
{
    //
    private $permission = array();

    public function getManagersEmployees(Request $request)
    {
        $paginationPage = ($request->PaginationPage - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;
        $year = $request->Year;
        $month = $request->Month;
        $employee = $request->AssignedID;
        $managerId = $request->Employee;
        //  dd($request->TitleID);


        $userEmployees = PerformanceResource::collection(EmployeePositionModel::Where(['ManagerID' => $managerId])->get());

        return response()->json($userEmployees);
    }

    public function test1(Request $request)
    {
        $request->validate([
            "EmployeeID" => 'required',
            "TechKnowledge" => 'required',
            "TimeManagement" => 'required',
            "Teamwork" => 'required',
            "MasteryOfTech" => 'required',
            "Responsibility" => 'required',
            "CommunicationSkills" => 'required',
            "CustomerFocus" => 'required',
            "SafeWorkProvider" => 'required',
            "ExitEffect" => 'required',
            "ExitEffectReason"=>'required'
            ]);

            $data = $request->except('token');
//             dd('test');
    //        PerformanceWeightModel::create($data);
//     return response()->json([
//                                                                             'success'=>true,
//                                                                             'message'=>$request->all()
//                                                                         ]);
            PerformanceWeightModel::updateOrCreate([
                'EmployeeID' => $request->EmployeeID,

            ], $request->except(['EmployeeID', 'token']));
            return response()->json([
                'success' => true,
                'message' => 'Başarıyla Eklendi'
            ]);
        }


}
