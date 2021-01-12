<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\OvertimeKindModel;
use App\Model\OvertimeModel;
use App\Model\OvertimeStatusModel;
use App\Model\ProjectsModel;
use App\Model\PublicHolidayModel;
use App\Model\UserProjectsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;


class OvertimeController extends ApiController
{

    public function getOvertimeById(Request $request)
    {
        $overtime = OvertimeModel::where(['id' => $request->OvertimeID, 'Active' => 1])->first();

        if (!$overtime)
            return response([
                'status' => false,
                'message' => 'Kayıt Bulunamadı',
            ], 200);

        if ($request->Page === "overtime") {
            if ($request->Employee !== $overtime->AssignedID)
                return response([
                    'status' => false,
                    'message' => 'Yetkisiz İşlem'
                ], 200);
        } elseif ($request->Page === "overtime-manager") {
            $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtime->AssignedID])->first();
            if ($employeePosition->UnitSupervisorID !== $request->Employee && $employeePosition->ManagerID !== $request->Employee)
                return response([
                    'status' => false,
                    'message' => 'Yetkisiz İşlem'
                ], 200);
        } elseif ($request->Page === "overtime-hr") {
            $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtime->AssignedID])->first();
            $hrPersonnels = EmployeePositionModel::where(['Active' => 2, 'RegionID' => $employeePosition->RegionID])->whereIn('TitleID', [98, 99, 100])->get();
            $idArray = [];
            foreach ($hrPersonnels as $hrPersonnel)
                array_push($idArray, $hrPersonnel->EmployeeID);
            if (!in_array($request->Employee, $idArray))
                return response([
                    'status' => false,
                    'message' => 'Yetkisiz İşlem'
                ], 200);
        }

        $data = [];
        array_push($data, $overtime);
        return response([
            'status' => true,
            'messsage' => 'İşlem Başarılı',
            'data' => $data
        ], 200);
    }

    public function getEmployeesOvertimeRequests(Request $request)
    {
        $status = isset($request->Status) || $request->Status != null ? $request->Status : null;

        if ($status == null) {
            $overtimes = OvertimeModel::where(['Active' => 1, 'AssignedID' => $request->Employee])->get();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $overtimes
            ], 200);
        }


        $overtimes = OvertimeModel::getEmployeesOvertimeByStatus($status, $request->Employee);
        $counts = OvertimeModel::selectRaw("StatusID AS statusVal, COUNT(*) AS count")->where(['Active' => 1, 'AssignedID' => $request->Employee])->groupBy("StatusID")->get();

        $amount = [];

        foreach ($counts as $count) {
            $amount['Status_' . $count->statusVal] = $count->count;
        }


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimes,
            'amounts' => $amount
        ], 200);

    }

    public function getOvertimeHRReports(Request $request)
    {

        $overtimes = [];

        if ($request->OvertimeStatus == 1 || $request->OvertimeStatus == 4) {
            $overtimeQ1 = OvertimeModel::whereYear("BeginDate", date("Y", strtotime($request->BeginDate)))
                ->whereMonth("BeginDate", date("m", strtotime($request->BeginDate)))
                ->where(['Active' => 1]);
            if ($request->EmployeeID)
                $overtimeQ1->where(['AssignedID' => $request->EmployeeID]);
            if ($request->KindID)
                $overtimeQ1->where(['KindID' => $request->KindID]);
            if ($request->OvertimeStatus)
                $overtimeQ1->where(['StatusID' => $request->OvertimeStatus]);

            $overtimes = $overtimeQ1->get();

        }
        else if ($request->OvertimeStatus == 6 || $request->OvertimeStatus == 8 || $request->OvertimeStatus == 9 || $request->OvertimeStatus == 10) {
            $overtimeQ2 = OvertimeModel::whereYear("WorkBeginDate", date("Y", strtotime($request->WorkBeginDate)))
                ->whereMonth("WorkBeginDate", date("m", strtotime($request->WorkBeginDate)))
                ->where(['Active' => 1]);
            if ($request->EmployeeID)
                $overtimeQ2->where(['AssignedID' => $request->EmployeeID]);
            if ($request->KindID)
                $overtimeQ2->where(['KindID' => $request->KindID]);
            if ($request->OvertimeStatus)
                $overtimeQ2->where(['StatusID' => $request->OvertimeStatus]);

            $overtimes = $overtimeQ2->get();

        }
        else
        {
            $overtimeQ1 = OvertimeModel::whereYear("BeginDate", date("Y", strtotime($request->BeginDate)))
                ->whereMonth("BeginDate", date("m", strtotime($request->BeginDate)))
                ->where(['Active' => 1]);
            if ($request->EmployeeID)
                $overtimeQ1->where(['AssignedID' => $request->EmployeeID]);
            if ($request->KindID)
                $overtimeQ1->where(['KindID' => $request->KindID]);

            $overtimeQ1->whereIn("StatusID", [1, 4]);

            $dataQ1 = $overtimeQ1->get();

            foreach ($dataQ1 as $item)
                array_push($overtimes,$item);

            $overtimeQ2 = OvertimeModel::whereYear("WorkBeginDate", date("Y", strtotime($request->WorkBeginDate)))
                ->whereMonth("WorkBeginDate", date("m", strtotime($request->WorkBeginDate)))
                ->where(['Active' => 1]);
            if ($request->EmployeeID)
                $overtimeQ2->where(['AssignedID' => $request->EmployeeID]);
            if ($request->KindID)
                $overtimeQ2->where(['KindID' => $request->KindID]);

            $overtimeQ2->whereIn("StatusID", [6, 8, 9, 10]);

            $dataQ2 = $overtimeQ2->get();

            foreach ($dataQ2 as $item)
                array_push($overtimes,$item);

        }


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimes
        ], 200);

    }

    public function getManagersOvertimeRequests(Request $request)
    {
        $status = isset($request->Status) || $request->Status != null ? $request->Status : null;

        if ($status == null) {
            $overtimes = OvertimeModel::where(['Active' => 1, 'ManagerID' => $request->Employee])->get();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $overtimes
            ], 200);
        }


        $overtimes = OvertimeModel::getOvertimeByStatus($status, $request->Employee);

        $userEmployees = EmployeePositionModel::where(['Active' => 2])->orWhere(['UnitSupervisorID' => $request->Employee, 'ManagerID' => $request->Employee])->get();
        $userEmployeesIDs = [];
        foreach ($userEmployees as $userEmployee) {
            array_push($userEmployeesIDs, $userEmployee->EmployeeID);
        }

        $employee = $request->Employee;

        $counts = OvertimeModel::selectRaw("StatusID AS statusVal, COUNT(*) AS count")->where(['Active' => 1])->where(function ($query) use ($employee) {
            $query->orWhere(['CreatedBy' => $employee]);
            $query->orWhere(['ManagerID' => $employee]);
        })->groupBy("StatusID")->get();

        $amount = [];

        foreach ($counts as $count) {
            $amount['Status_' . $count->statusVal] = $count->count;
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimes,
            'dataCounts' => $amount
        ], 200);

    }

    public function getManagersEmployees(Request $request)
    {
        $manager = EmployeeModel::find($request->Employee);
        $employees = OvertimeModel::getManagersEmployees($manager->Id);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees
        ], 200);
    }

    public function getHREmployees(Request $request)
    {
        $employees = OvertimeModel::getHREmployees($request);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees
        ], 200);
    }

    public function getEmployeesManagers(Request $request)
    {
        $employee = EmployeeModel::find($request->Employee);
        $managers = OvertimeModel::getEmployeesManagers($employee->Id);
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $managers
        ], 200);
    }

    public function overtimeKinds()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => OvertimeKindModel::all()
        ], 200);
    }

    public function managersProjectList(Request $request)
    {
        $employeeID = isset($request->EmployeeID) ? $request->EmployeeID : $request->Employee;
        $managersProjects = UserProjectsModel::where(['Active' => 1, 'EmployeeID' => $employeeID])->get();
        $managerProjectList = [];

        foreach ($managersProjects as $managersProject) {
            $temp = ProjectsModel::find($managersProject->project_id);
            array_push($managerProjectList, $temp);
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $managerProjectList
        ], 200);
    }

    public function getOvertimeLimits(Request $request)
    {

        $data = OvertimeModel::overtimeRemainingLimits($request);

        return response([
            'status' => true,
            'data' => $data
        ], 200);

    }

    public function getCarLocation(Request $request)
    {
        $guzzleParams = [

            'headers' => [
                'Mobiliz-Token' => '9d2a16548c761255458ad0e82fe2b7d9283e6195a104fc314d1520c0c181d564',
            ],
            "query" =>
                [
                    "plate" => "34CZF877",
                    "startTime" => date(DATE_ATOM, strtotime("2020-10-11 09:00:00")),
                    "endTime" => date(DATE_ATOM, strtotime("2020-10-11 15:00:00"))
                ]

        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request("GET", 'https://ng.mobiliz.com.tr/su6/api/integrations/locations', $guzzleParams);
        $responseBody = json_decode($res->getBody());

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $responseBody
        ], 200);

    }

    public function saveOvertimeRequest(Request $request)
    {

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
            ], 200);

        if (isset($request->WorkBeginTime) && isset($request->WorkEndTime))
            if (strtotime($request->WorkBeginTime) > strtotime($request->WorkEndTime))
                return response([
                    'status' => false,
                    'message' => 'İş Başlangıç saati, bitiş saatinden büyük olamaz.'
                ], 200);


        $status = OvertimeModel::saveOvertimeByProcessType($request->processType, $request);

        if ($status['status'])
            return response([
                'status' => true,
                'message' => $status['message'],
            ], 200);

        return response([
            'status' => false,
            'message' => $status['message'],
        ], 200);


    }

    public function getRemainingOvertimeLimits(Request $request)
    {

        $assignedId = $request->assignedId;

        if (!isset($assignedId) || $assignedId == "" || $assignedId == null)
            return response([
                'status' => false,
                'message' => 'Atanan kişinin Idsi boş olamaz'
            ], 200);

        $data = OvertimeModel::getRemainingOvertimeLimits($request);

    }

    public function getOvertimeKindByDate(Request $request)
    {


        $beginDate = Carbon::createFromFormat("Y-m-d", $request->BeginDate);

        $publicHolidayRecCount = PublicHolidayModel::whereDate('end_date', ">", $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day)
            ->whereRaw('? >= DATE(start_date)', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day])
            ->count();
        if ($publicHolidayRecCount > 0) {

            return response([
                'status' => true,
                'message' => 'Resmi Tatil',
                'data' => 3
            ], 200);
        }

        $weekDay = date('w', strtotime($request->BeginDate));
        if ($weekDay == 0) {
            return response([
                'status' => true,
                'message' => 'Hafta Sonu',
                'data' => 2
            ], 200);
        }

        return response([
            'status' => true,
            'message' => 'Is Gunu',
            'data' => 1
        ], 200);


    }


}
