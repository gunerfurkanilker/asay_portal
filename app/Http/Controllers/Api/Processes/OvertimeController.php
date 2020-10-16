<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\OvertimeKindModel;
use App\Model\OvertimeModel;
use App\Model\OvertimeStatusModel;
use App\Model\ProjectsModel;
use App\Model\UserProjectsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;


class OvertimeController extends ApiController
{

    public function getEmployeesOvertimeRequests(Request $request)
    {
        $status = isset($request->Status) || $request->Status != null ? $request->Status : null;

        if ($status == null)
        {
            $overtimes = OvertimeModel::where([ 'Active' => 1,'AssignedID' => $request->Employee ])->get();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $overtimes
            ],200);
        }


        $overtimes = OvertimeModel::getEmployeesOvertimeByStatus($status,$request->Employee);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimes
        ],200);

    }

    public function getManagersOvertimeRequests(Request $request)
    {
        $status = isset($request->Status) || $request->Status != null ? $request->Status : null;

        if ($status == null)
        {
            $overtimes = OvertimeModel::where([ 'Active' => 1,'ManagerID' => $request->Employee ])->get();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $overtimes
            ],200);
        }


        $overtimes = OvertimeModel::getOvertimeByStatus($status,$request->Employee);

        $userEmployees = EmployeePositionModel::where(['Active' => 2])->orWhere(['UnitSupervisorID' => $request->Employee, 'ManagerID' => $request->Employee])->get();
        $userEmployeesIDs = [];
        foreach ($userEmployees as $userEmployee) {
            array_push($userEmployeesIDs, $userEmployee->EmployeeID);
        }


        $counts = OvertimeModel::selectRaw("StatusID AS statusVal, COUNT(*) AS count")->where(['Active' => 1])->groupBy("StatusID")->get();
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimes,
            'dataCounts' => $counts
        ],200);

    }

    public function getManagersEmployees(Request $request)
    {
        $manager = EmployeeModel::find($request->Employee);
        $employees = OvertimeModel::getManagersEmployees($manager->Id);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees
        ],200);
    }

    public function getHREmployees(Request $request)
    {
        $employees = OvertimeModel::getHREmployees($request);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees
        ],200);
    }

    public function getEmployeesManagers(Request $request){
        $employee = EmployeeModel::find($request->Employee );
        $managers = OvertimeModel::getEmployeesManagers($employee->Id);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $managers
        ],200);
    }

    public function overtimeKinds()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => OvertimeKindModel::all()
        ],200);
    }

    public function managersProjectList(Request $request)
    {
        $managersProjects = UserProjectsModel::where(['Active' => 1, 'EmployeeID' => $request->Employee ])->get();
        $managerProjectList = [];

        foreach($managersProjects as $managersProject)
        {
            $temp = ProjectsModel::find($managersProject->project_id);
            array_push($managerProjectList,$temp);
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $managerProjectList
        ],200);
    }

    public function getOvertimeLimits(Request $request){

        $data = OvertimeModel::overtimeRemainingLimits($request);

        return response([
            'status' => true,
            'data' => $data
        ],200);

    }

    public function saveOvertimeRequest(Request $request){

        /*
         * Request Tipleri
         * Tip 0 : Fazla Çalışmayı kaydetme durumu
         * Tip 1 : Yöneticiden çalışana fazla çalışma atama durumu
         * Tip 2 : Çalışandan yöneticiye düzeltme talebi
         * Tip 3 : Çalışan tarafından reddedildi -> Yöneticiye düzeltme gidecek.
         * Tip 4 : Çalışan tarafından onaylandı
         * Tip 5 : Çalışan tarafından iptal edildi
         * Tip 6 : Çalışan tarafından çalışma tamamlandı -> Yönetici Onayı Bekleniyor.
         * Tip 7 : Yönetici tarafından fazla çalışmaya yönetici tarafından düzeltme talep edildi.
         * Tip 8 : Yönetici tarafından fazla çalışma onaylandı.
         * Tip 9 : IK tarfından fazla çalışmaya düzenleme talebi yapıldı.
         * Tip 10 : IK tarafından onaylandı
         *
         * */
        /*if(!isset($request->processType) || $request->processType == null || $request->processType == "")
        {
            return response([
                'status' => false,
                'message' => 'İşlem Tipi Tanımlanmamış'
            ],200);
        }*/
        /*return response([
            'status' => true,
            'message' => $request->all(),
        ],200);*/


        if (strtotime($request->BeginTime) > strtotime($request->EndTime))
            return response([
                'status' => false,
                'message' => 'Başlangıç saati, bitiş saatinden büyük olamaz.'
            ],200);

        if (isset($request->WorkBeginTime) && isset($request->WorkEndTime))
            if (strtotime($request->WorkBeginTime) > strtotime($request->WorkEndTime))
                return response([
                    'status' => false,
                    'message' => 'İş Başlangıç saati, bitiş saatinden büyük olamaz.'
                ],200);


        $status = OvertimeModel::saveOvertimeByProcessType($request->processType,$request);

        if ($status['status'])
            return response([
                'status' => true,
                'message' => $status['message'],
            ],200);

        return response([
            'status' => false,
            'message' => $status['message'],
        ],200);



    }

    public function getRemainingOvertimeLimits(Request $request){

        $assignedId = $request->assignedId;

        if (!isset($assignedId) || $assignedId == "" || $assignedId == null)
            return response([
                'status' => false,
                'message' => 'Atanan kişinin Idsi boş olamaz'
            ],200);

        $data = OvertimeModel::getRemainingOvertimeLimits($request);

    }


}
