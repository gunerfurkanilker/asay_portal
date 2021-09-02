<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\LogsModel;
use App\Model\NotificationsModel;
use App\Model\OvertimeKindModel;
use App\Model\OvertimeModel;
use App\Model\OvertimeStatusModel;
use App\Model\ProjectsModel;
use App\Model\PublicHolidayModel;
use App\Model\UserProjectsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class OvertimeController extends ApiController
{

    public function overtimeReportsToExcel(Request $request){

        $spreadsheet = new Spreadsheet();

        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $workSheet = new Worksheet();

        $columns = [
            'TC Kimlik No',
            'Atayan Kişi',
            'Çalışmayı Atama Tarihi',
            'Çalışmanın Onaylandığı Tarih',
            'İK Birimi Tarafından Onaylanma Tarihi',
            'Atanan Kişi',
            'Çalışmanın Kabul Edildiği Tarih',
            'Çalışmanın Onaya Gönderildiği Tarih',
            'Hizmet Kodu',
            'Departman',
            'Proje',
            'Şehir',
            'Çalışma Türü',
            'Çalışma Tarihi',
            'Çalışma Başlangıç Saati',
            'Çalışma Bitiş Saati',
            'Çalışma Yapılacak Saha ID',
            'Çalışma Yapılacak Saha Adı',
            'Çalışma No',
            'İş Emri No',
            'Araç Kullanacak Mı ?',
            'Araç Plakası',
            'Açıklama',
            'Durumu'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."1",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }

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


            $overtimes = $overtimeQ1->orderBy("BeginDate","desc")->get();



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

            $overtimeCountQ = $overtimeQ2;

            $overtimes = $overtimeQ2->orderBy("WorkBeginDate","desc")->get();


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

            $dataQ1 = $overtimeQ1->orderBy("BeginDate","desc")->get();

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

            $dataQ2 = $overtimeQ2->orderBy("WorkBeginDate","desc")->get();

            foreach ($dataQ2 as $item)
                array_push($overtimes,$item);

        }

        foreach ($overtimes as $key => $overtime)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];

            if($overtime->AssignedEmployee)
            {
                $tcKimlikNo = $overtime->AssignedEmployee->IDCard ? $overtime->AssignedEmployee->IDCard->TCNo : '' ;
                $createdBy = $overtime->CreatedByEmployee->UsageName . ' ' . $overtime->CreatedByEmployee->LastName;
                $assignedTo = $overtime->AssignedEmployee->UsageName . ' ' . $overtime->AssignedEmployee->LastName;
                $serviceCode = $overtime->AssignedEmployee->EmployeePosition->ServiceCode;
                $department = $overtime->AssignedEmployee->EmployeePosition->Department->Sym;
            }
            $ovReqAssignDate = NotificationsModel::where(['EmployeeID' => $overtime->AssignedID, 'ObjectID' => $overtime->id, 'ObjectType' => 4])
                ->where("Content","like",'%fazla çalışma için onayınız bekleniyor%')
                ->first();
            $ovReqAssignDate = $ovReqAssignDate ? $ovReqAssignDate->created_at : '';
            $ovReqEmployeeConfirmDate = LogsModel::where(['LogType' => 24, 'ObjectType' => 4, 'ObjectId' => $overtime->id, 'EmployeeID' => $overtime->AssignedID])->first();
            $ovReqEmployeeConfirmDate = $ovReqEmployeeConfirmDate ? $ovReqEmployeeConfirmDate->StartDate : '';
            $ovReqEmployeeCompleteDate = LogsModel::where(['LogType' => 27, 'ObjectType' => 4, 'ObjectId' => $overtime->id, 'EmployeeID' => $overtime->AssignedID])->first();
            $ovReqEmployeeCompleteDate = $ovReqEmployeeCompleteDate ? $ovReqEmployeeCompleteDate->StartDate : '';
            $ovReqManagerConfirmDate = LogsModel::where(['LogType' => 28, 'ObjectType' => 4, 'ObjectId' => $overtime->id])->first();
            $ovReqManagerConfirmDate = $ovReqManagerConfirmDate ? $ovReqManagerConfirmDate->StartDate : '';
            $ovReqHRConfirmDate = LogsModel::where(['LogType' => 29, 'ObjectType' => 4, 'ObjectId' => $overtime->id])->first();
            $ovReqHRConfirmDate = $ovReqHRConfirmDate ? $ovReqHRConfirmDate->StartDate : '';
            $project = $overtime->Project->name;
            $city = $overtime->City->Sym;
            $overtimeKind = $overtime->Kind->Name;
            $overtimeDate = $overtime->WorkBeginDate ? $overtime->WorkBeginDate : $overtime->BeginDate;
            $overtimeStartTime = $overtime->WorkBeginTime ? $overtime->WorkBeginTime : $overtime->BeginTime;
            $overtimeEndTime = $overtime->WorkEndTime ? $overtime->WorkEndTime : $overtime->EndTime;
            $fieldID = $overtime->FieldID;
            $fieldName = $overtime->FieldName;
            $workNo = $overtime->WorkNo;
            $jobOrderNo = $overtime->JobOrderNo;
            $usingCar = $overtime->UsingCar == 1 ? 'Evet' : 'Hayır';
            $carPlate = $overtime->PlateNumber;
            $description = $overtime->Description;
            $status = "";
            switch ($overtime->StatusID)
            {
                case 0:
                    $status = "Kaydedildi";
                    break;
                case 1:
                    $status = "Çalışan Onayı Bekleniyor";
                    break;
                case 2:
                    $status = "Çalışan Düzenleme Talebi";
                    break;
                case 3:
                    $status = "Çalışan Tarafından Reddedildi";
                    break;
                case 4:
                    $status = "Çalışan Tarafından Onaylandı";
                    break;
                case 5:
                    $status = "Yönetici Tarafından İptal Edildi";
                    break;
                case 6:
                    $status = "Yönetici Onayı Bekleniyor";
                    break;
                case 7:
                    $status = "Yönetici Düzenleme Talebi";
                    break;
                case 8:
                    $status = "Yönetici Tarafından Onaylandı";
                    break;
                case 9:
                    $status = "IK Düzenleme Talebi";
                    break;
                case 10:
                    $status = "IK Tarafından Onaylandı";
                    break;
            }

            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,isset($tcKimlikNo) ? $tcKimlikNo : '');
            array_push($values,isset($createdBy) ? $createdBy : '');
            array_push($values,$ovReqAssignDate);
            array_push($values,$ovReqManagerConfirmDate);
            array_push($values,$ovReqHRConfirmDate);
            array_push($values,isset($assignedTo) ? $assignedTo : '');
            array_push($values,$ovReqEmployeeConfirmDate);
            array_push($values,$ovReqEmployeeCompleteDate);
            array_push($values,isset($serviceCode) ? $serviceCode : '');
            array_push($values,isset($department) ? $department : '');
            array_push($values,$project);
            array_push($values,$city);
            array_push($values,$overtimeKind);
            array_push($values,$overtimeDate);
            array_push($values,$overtimeStartTime);
            array_push($values,$overtimeEndTime);
            array_push($values,$fieldID);
            array_push($values,$fieldName);
            array_push($values,$workNo);
            array_push($values,$jobOrderNo);
            array_push($values,$usingCar);
            array_push($values,$carPlate);
            array_push($values,$description);
            array_push($values,$status);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($key+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        $spreadsheet->addSheet($workSheet,0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("Employees.xlsx", $content);
        return response()->download(storage_path('app/' . "Employees.xlsx"));


    }

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


        $paginationPage = ($request->PaginationPage - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;

        $overtimeData = OvertimeModel::getEmployeesOvertimeByStatus($status, $request->Employee,$paginationPage,$recordPerPage);
        $counts = OvertimeModel::selectRaw("StatusID AS statusVal, COUNT(*) AS count")->where(['Active' => 1, 'AssignedID' => $request->Employee])->groupBy("StatusID")->get();

        $amount = [];

        foreach ($counts as $count) {
            $amount['Status_' . $count->statusVal] = $count->count;
        }


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimeData['overtimes'],
            'dataCount' => $overtimeData['singleStatusCount'],
            'amounts' => $amount
        ], 200);

    }

    public function getOvertimeHRReports(Request $request)
    {
        $overtimeData = [];
        $paginationPage = ($request->PaginationPage - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;


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

            $overtimeCountQ = $overtimeQ1;
            $overtimeData = [
                'singleStatusCount' => $overtimeCountQ->count(),
                'overtimes' => $overtimeQ1->orderBy("BeginDate","desc")->offset($paginationPage)->take($recordPerPage)->get(),

            ];


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

            $overtimeCountQ = $overtimeQ2;
            $overtimeData = [
                'singleStatusCount' => $overtimeCountQ->count(),
                'overtimes' => $overtimeQ2->orderBy("WorkBeginDate","desc")->offset($paginationPage)->take($recordPerPage)->get(),

            ];

        }
        else
        {

            $overtimes =[];

            $overtimeQ1 = OvertimeModel::whereYear("BeginDate", date("Y", strtotime($request->BeginDate)))
                ->whereMonth("BeginDate", date("m", strtotime($request->BeginDate)))
                ->where(['Active' => 1]);
            if ($request->EmployeeID)
                $overtimeQ1->where(['AssignedID' => $request->EmployeeID]);
            if ($request->KindID)
                $overtimeQ1->where(['KindID' => $request->KindID]);

            $overtimeQ1->whereIn("StatusID", [1, 4]);

            $overtimeCountQ1 = $overtimeQ1->count();


            $dataQ1 = $overtimeQ1->orderBy("BeginDate","desc")->offset($paginationPage)->take($recordPerPage/2)->get();

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

            $overtimeCountQ2 = $overtimeQ2->count();

            $dataQ2 = $overtimeQ2->orderBy("WorkBeginDate","desc")->offset($paginationPage)->take($recordPerPage/2)->get();

            foreach ($dataQ2 as $item)
                array_push($overtimes,$item);

            $overtimeData = [
                'singleStatusCount' => $overtimeCountQ1 + $overtimeCountQ2,
                'overtimes' => $overtimes,
            ];

        }


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimeData['overtimes'],
            'dataCount' => $overtimeData['singleStatusCount']
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

        $paginationPage = ($request->PaginationPage - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;

        $year = $request->Year;
        $month = $request->Month;
        $employee = $request->AssignedID;

        $overtimeData = OvertimeModel::getOvertimeByStatus($year,$month,$employee,$status, $request->Employee,$paginationPage,$recordPerPage);



        $userEmployees = EmployeePositionModel::where(['Active' => 2])->orWhere(['UnitSupervisorID' => $request->Employee, 'ManagerID' => $request->Employee])->get();
        $userEmployeesIDs = [];
        foreach ($userEmployees as $userEmployee) {
            array_push($userEmployeesIDs, $userEmployee->EmployeeID);
        }

        $employee = $request->Employee;

        $counts = OvertimeModel::selectRaw("StatusID AS statusVal, COUNT(*) AS count")->where(['Active' => 1])->where(function ($query) use ($employee,$status) {
            $query->where(['CreatedBy' => $employee, 'ManagerID' => $employee]);
        })->groupBy("StatusID")->get();

        $amount = [];

        foreach ($counts as $count) {
            $amount['Status_' . $count->statusVal] = $count->count;
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $overtimeData['overtimes'],
            'dataCounts' => $amount,
            'dataCount' => $overtimeData['singleStatusCount'],
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
        $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $request->EmployeeID])->first();
        $projectsArray = [];

        switch ($employeePosition->OrganizationID)
        {
            case 4:
                $projectsArray = [1];
                break;
            default:
                $projectsArray = [1,2];
        }


        $projects = ProjectsModel::where(['active' => 1])->whereIn("id",$projectsArray)->get();



        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $projects
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

        $dateCheck = OvertimeModel::dateCheck($request);


        if(!$dateCheck)
            return response([
                'status' => false,
                'message' => 'Bu kayıt geçmiş döneme ait bir kayıttır, işlem yapılamaz',
                'test' => $dateCheck
            ], 200);


        $status = OvertimeModel::saveOvertimeByProcessType($request->processType, $request);

        if ($status['status'])
            return response([
                'status' => true,
                'message' => $status['message'],
            ], 200);

        return response([
            'status' => false,
            'message' => $status['message']
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
