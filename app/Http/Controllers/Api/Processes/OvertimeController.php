<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\OvertimeKindModel;
use App\Model\OvertimeModel;
use App\Model\OvertimeStatusModel;
use App\Model\ProjectsModel;
use App\Model\UserModel;
use App\Model\UserProjectsModel;
use Illuminate\Http\Request;


class OvertimeController extends ApiController
{

    public function getOvertimeRequests(Request $request)
    {
        $status = isset($request->Status) || $request->Status != null ? $request->Status : null;

        if ($status == null)
        {
            $user = UserModel::find($request->userId);
            $overtimes = OvertimeModel::where([ 'Active' => 1,'ManagerID' => $user->EmployeeID ])->get();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $overtimes
            ],200);
        }


        $overtimes = OvertimeModel::getOvertimeByStatus($status,$request->userId);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimes
        ],200);

    }

    public function getManagersEmployees(Request $request)
    {
        $manager = EmployeeModel::find(UserModel::find( $request->userId)->EmployeeID );
        $employees = OvertimeModel::getManagersEmployees($manager->Id);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees
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
        $user = UserModel::find($request->userId);
        $managersProjects = UserProjectsModel::where(['Active' => 1, 'EmployeeID' => $user->EmployeeID ])->get();
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

    public function saveOvertimeRequest(Request $request){

        /*
         * Request Tipleri
         *
         * Tip 1 : Yöneticiden çalışana fazla çalışma atama durumu
         * Tip 2 : Çalışandan yöneticiye düzeltme talebi
         * Tip 3 : Çalışan tarafından reddedildi -> Yöneticiye düzeltme gidecek.
         * Tip 4 : Çalışan tarafından onaylandı -> Yönetici Onayı Bekleniyor.
         * Tip 5 : Çalışan tarafından iptal edildi
         * Tip 6 : Çalışan tarafından çalışma tamamlandı
         * Tip 7 : Yönetici tarafından fazla çalışma onaylandı.
         * Tip 8 : Yönetici tarafından fazla çalışmaya yönetici tarafından düzeltme talep edildi.
         * Tip 9 : IK tarfından fazla çalışmaya düzenleme talebi yapıldı.
         * Tip 10 : IK tarafından onaylandı
         *
         * */
        if(!isset($request->processType) || $request->processType == null || $request->processType == "")
        {
            return response([
                'status' => false,
                'message' => 'İşlem Tipi Tanımlanmamış'
            ],200);
        }

        $status = OvertimeModel::saveOvertimeByProcessType($request->processType,$request->all());

        if ($status)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
            ],200);

        return response([
            'status' => false,
            'message' => 'İşlem Başarısız',
        ],200);



    }



}
