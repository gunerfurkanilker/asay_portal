<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\LogsModel;
use App\Model\NotificationsModel;
use App\Model\OvertimeRestModel;
use App\Model\PermitKindModel;
use App\Model\PermitLeftOverHoursModel;
use App\Model\PermitModel;
use App\Model\ProcessesSettingsModel;
use App\Model\PublicHolidayModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use SoapClient;
use Whoops\Util\TemplateHelper;

class PermitController extends ApiController
{

    public function getPermitById(Request $request)
    {
        $permit = PermitModel::where(['id' => $request->PermitID, 'Active' => 1])->first();

        if (!$permit)
            return response([
                'status' => false,
                'message' => 'Kayıt Bulunamadı',
            ],200);

        if($request->Page === "my-permits")
        {
            if ($request->Employee !== $permit->EmployeeID)
                return response([
                    'status' => false,
                    'message' => 'Yetkisiz İşlem'
                ],200);
        }

        elseif ($request->Page === "permits")
        {

            if ($permit->status == 1)
            {
                $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $permit->EmployeeID])->first();
                if ($employeePosition->ManagerID !== $request->Employee)
                    return response([
                        'status' => false,
                        'message' => 'Yetkisiz İşlem'
                    ],200);
            }
            elseif ($permit->status == 2)
            {
                $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $permit->EmployeeID])->first();
                $hrPersonnels = ProcessesSettingsModel::where(['RegionID' => $employeePosition->RegionID,'object_type' => 3, 'PropertyCode' => 'HRManager'])->get();
                $idArray = [];
                foreach ($hrPersonnels as $hrPersonnel)
                    array_push($idArray,(int)$hrPersonnel->PropertyValue);
                if(!in_array($request->Employee,$idArray))
                    return response([
                        'status' => false,
                        'message' => 'Yetkisiz İşlem'
                    ],200);

            }
            elseif ($permit->status == 3)
            {
                $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $permit->EmployeeID])->first();
                $personnelSpecialists = ProcessesSettingsModel::where(['RegionID' => $employeePosition->RegionID,'object_type' => 3, 'PropertyCode' => 'PersonnelSpecialist'])->get();
                $idArray = [];
                foreach ($personnelSpecialists as $personnelSpecialist)
                    array_push($idArray,(int)$personnelSpecialist->PropertyValue);
                if(!in_array($request->Employee,$idArray))
                    return response([
                        'status' => false,
                        'message' => 'Yetkisiz İşlem'
                    ],200);
            }


        }

        $data = [];
        array_push($data,$permit);
        return response([
            'status' => true,
            'messsage' => 'İşlem Başarılı',
            'data' => $data
        ], 200);
    }

    public function permitList(Request $request)
    {
        $employee = EmployeeModel::find($request->Employee);

        if (!isset($request->status) || $request->status == null)
        {
            $permitQ = PermitModel::where(['active' => 1, 'EmployeeID' => $employee->Id]);
            $request->Correction == 'true' ? $permitQ->where(['correction_status' => 1]) : $permitQ->where(['correction_status' => 0]);
            $permits = $permitQ->get();
            return response([
                'status' => true,
                'message' => "İşlem Başarılı",
                'data' => $permits
            ], 200);
        }


        else
        {
            $permitQ = PermitModel::where(['EmployeeID' => $employee->Id, 'status' => $request->status, 'active' => 1]);
            $request->Correction == 'true' ? $permitQ->where(['correction_status' => 1]) : $permitQ->where(['correction_status' => 0]);
            $permits = $permitQ->get();
            return response([
                'status' => true,
                'message' => "İşlem Başarılı",
                'data' => $permits
            ], 200);
        }



    }

    public function getTransferPersons(Request $request)
    {
        $employee = EmployeeModel::find($request->Employee);
        $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $employee->Id])->first();

        $transferEmployeesPositions = EmployeePositionModel::where(['Active' => 2, 'RegionID' => $employeePosition->RegionID])
            ->whereNotIn('EmployeeID',[$request->Employee])->get();

        $transferEmployees = [];

        foreach ($transferEmployeesPositions as $transferEmployeesPosition) {
            $tempEmployee = DB::table("Employee")->find($transferEmployeesPosition->EmployeeID);
            if ($tempEmployee)
                array_push($transferEmployees, $tempEmployee);
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $transferEmployees
        ], 200);

    }

    public function getPermit(Request $request)
    {
        $employee = EmployeeModel::find($request->Employee);
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => PermitModel::where(["id" => $request->permitId, 'EmployeeID' => $employee->Id])->first()
        ], 200);
    }

    public function permitTypes(Request $request)
    {
        $permitKinds = PermitKindModel::where(["active" => 1])->orderBy("order","desc")->get();

        $employeeOvertimeRestTotalHour = OvertimeRestModel::selectRaw("SUM(Hour) as total_hour,Sum(Minute) as total_minute")->where(['EmployeeID' => isset($request->fromHR) ? $request->EmployeeID : $request->Employee, 'Active' => 1])
            ->whereYear("Date", "=", date('Y'))->first();
        $totalRestPermits = PermitModel::selectRaw("SUM(used_day) as total_day,SUM(over_hour) as over_hour,SUM(over_minute) as total_minute")->where(['Active' => 1, 'EmployeeID' => isset($request->fromHR) ? $request->EmployeeID : $request->Employee, 'kind' => 14])
            ->whereYear("start_date", "=", date('Y'))->first();

        $employeeOvertimeRestTotalMinute = ($employeeOvertimeRestTotalHour->total_hour * 60) + $employeeOvertimeRestTotalHour->total_minute;
        $totalRestPermitsMinute = ($totalRestPermits->total_day*8*60) + ($totalRestPermits->over_hour*60) + $totalRestPermits->total_minute;

        $remainingRestPermitTotalMinute = $employeeOvertimeRestTotalMinute - $totalRestPermitsMinute;

        $remainingRestPermitDay = (int) (((int) $remainingRestPermitTotalMinute / 60) / 8);
        $remainingRestPermitMinute = $remainingRestPermitTotalMinute % 60;
        $remainingRestPermitHour = (int) (((int) $remainingRestPermitTotalMinute / 60) % 8);


        $restPermitRemainingYear = $remainingRestPermitDay . ' gün, ' . $remainingRestPermitHour . ' saat, ' . ($remainingRestPermitMinute) . 'dakika';

        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => $permitKinds,
            'restPermitRemaining' => $restPermitRemainingYear
        ], 200);
    }


    public function savePermit(Request $request)
    {
        if ($request->permitId !== null) {
            $EmployeeID = PermitModel::find($request->permitId)->EmployeeID;
        } else
            $EmployeeID = $request->Employee;
        //tanımlar
        $endDate = $request->endDate;
        $startDate = $request->startDate;

        if ($startDate > $endDate) {
            return response([
                'status' => false,
                'message' => "İzin Tarihlerini Kontrol Ediniz. Hatalı Girdiniz."
            ], 200);
        }

        $calculatePermit = PermitModel::calculatePermit($startDate, $endDate);
        if ($calculatePermit['UsedDay'] == 0 && $calculatePermit['OverHour'] == 0 && $calculatePermit['OverMinute'] == 0 && $calculatePermit['Holidays'] > 0) // Resmi tatil günü izin almaya çalışırsa
            return response([
                'status' => false,
                'message' => 'Resmi tatil günü izin alamazsınız'
            ], 200);
        if ($calculatePermit['UsedDay'] == 0 && $calculatePermit['OverHour'] == 0 && $calculatePermit['OverMinute'] == 0)
            return response([
                'status' => false,
                'message' => 'Başlangıç ve bitiş tarihi ve zamanı aynı olan talep oluşturamazsınız'
            ], 200);

        $requestedPermit = $calculatePermit['UsedDay'] . ' gün, ' . $calculatePermit['OverHour'] . ' saat, ' . ($calculatePermit['OverMinute']) . 'dakika';

        if ($request->kind == 12) {
            //Yıllık İzin ise
            $remainingDays = PermitModel::netsisRemainingPermit($EmployeeID);
            if (($calculatePermit["UsedDay"] > $remainingDays['daysLeft']) ||
                ($calculatePermit["UsedDay"] == $remainingDays['daysLeft'] && $calculatePermit["OverHour"] > $remainingDays['hoursLeft'])) {
                return response([
                    'status' => false,
                    'message' => "Yıllık izin hakkınızdan fazla bir izin talep ettiniz.\n Kullandığınız izin miktarı : "
                        . $calculatePermit["UsedDay"] . ' gün, ' . $calculatePermit["OverHour"] . 'saat.' . '\n Kalan İzin Miktarı : ' . $remainingDays['daysLeft'] . ' gün, ' . $remainingDays['hoursLeft'] . ' saat.',
                ], 200);
            }
        } else {
            $getLimitOfKind = PermitKindModel::where(['Active' => 1, 'id' => $request->kind])->first();

            switch ($request->kind) {
                case 2://Babalık izni
                    if ($calculatePermit['UsedDay'] > $getLimitOfKind->dayLimitPerRequest)
                        return response([
                            'status' => false,
                            'message' => "Talep ettiğiniz izin tarihleri, babalık izni limitlerini aşmaktadır. Lütfen izin başlangıç ve bitiş tarihlerini kontrol ediniz\nTalep ettiğiniz izin : " . $requestedPermit
                        ], 200);
                    break;
                case 3:
                    if ($calculatePermit['UsedDay'] > $getLimitOfKind->dayLimitPerRequest)
                        return response([
                            'status' => false,
                            'message' => "Talep ettiğiniz izin tarihleri, doğum izni limitlerini aşmaktadır. Lütfen izin başlangıç ve bitiş tarihlerini kontrol ediniz\nTalep ettiğiniz izin : " .$requestedPermit
                        ], 200);
                    break;
                case 4:
                    if ($calculatePermit['UsedDay'] > $getLimitOfKind->dayLimitPerRequest)
                        return response([
                            'status' => false,
                            'message' => "Talep ettiğiniz izin tarihleri, doğum sonrası izni limitlerini aşmaktadır. Lütfen izin başlangıç ve bitiş tarihlerini kontrol ediniz\nTalep ettiğiniz izin : " .$requestedPermit
                        ], 200);
                    break;
                case 5:
                    if ($calculatePermit['UsedDay'] > $getLimitOfKind->dayLimitPerRequest)
                        return response([
                            'status' => false,
                            'message' => "Talep ettiğiniz izin tarihleri, evlilik izni limitlerini aşmaktadır. Lütfen izin başlangıç ve bitiş tarihlerini kontrol ediniz\nTalep ettiğiniz izin : " . $requestedPermit
                        ], 200);
                    break;
                case 6:
                    $totalIllnessPermits = PermitModel::selectRaw("SUM(used_day) as total_day,SUM(over_hour) as over_hour,SUM(over_minute) as over_minute")->where(['Active' => 1, 'EmployeeID' => $EmployeeID, 'kind' => $request->kind])
                        ->where('id', "<>", $request->permitId)
                        ->whereYear("start_date", "=", $request->startDate)->first();
                    $totalIllnessPermitMinute = ($totalIllnessPermits->total_day*8*60) + ($totalIllnessPermits->over_hour*60) + $totalIllnessPermits->over_minute;
                    $requestedIllnessPermitMinute = ($calculatePermit['UsedDay'] * 8 * 60) + ($calculatePermit['OverHour']*60) + $calculatePermit['OverMinute'];

                    $remainingMinutes = (($getLimitOfKind->dayLimitPerYear*8*60) - ($totalIllnessPermitMinute)) % 60;
                    $remainingDays = (int) ((int)(($getLimitOfKind->dayLimitPerYear*8*60) - ($totalIllnessPermitMinute)) / 60) / 8;
                    $remainingHours =(int) ((int)(($getLimitOfKind->dayLimitPerYear*8*60) - ($totalIllnessPermitMinute)) / 60) % 8;


                    if ($totalIllnessPermitMinute + $requestedIllnessPermitMinute > ($getLimitOfKind->dayLimitPerYear*8*60))
                        return response([
                            'status' => false,
                            'message' => "Hastalık izni hakkınız kalmamıştır\n"."Kalan hakkınız : ".$remainingDays." gün, ".$remainingHours." saat,".$remainingMinutes." dakika"
                        ],200);
                    break;
                case 7:
                    if ($calculatePermit['UsedDay'] < 1) {
                        if ($calculatePermit['OverHour'] > 2)
                            return response([
                                'status' => false,
                                'message' => 'Günlük İş arama izni hakkınız 2 saat ile sınırlıdır.'
                            ], 200);
                        else if ($calculatePermit['OverHour'] == 2 && $calculatePermit['OverMinute'] > 0)
                            return response([
                                'status' => false,
                                'message' => 'Günlük İş arama izni hakkınız 2 saat ile sınırlıdır.'
                            ], 200);
                    }
                    break;
                case 11:
                    if ($calculatePermit['UsedDay'] > $getLimitOfKind->dayLimitPerRequest)
                        return response([
                            'status' => false,
                            'message' => 'Talep ettiğiniz izin tarihleri, vefat izni limitlerini aşmaktadır. Lütfen izin başlangıç ve bitiş tarihlerini kontrol ediniz.'
                        ], 200);
                    break;

            }
        }

        //Dinlenme izni talep edildi ise
        if ($request->kind == 14) {
            $permitRest = PermitKindModel::find($request->kind);
            $permitBeginDate = Carbon::createFromFormat("Y-m-d", explode(" ", $request->startDate)[0]);
            $permitEndDate = Carbon::createFromFormat("Y-m-d", explode(" ", $request->endDate)[0]);


            $employeeOvertimeRestTotalHour = OvertimeRestModel::selectRaw("SUM(Hour) as total_hour,Sum(Minute) as total_minute")->where(['EmployeeID' => $EmployeeID, 'Active' => 1])->where('id', "<>", $request->permitId)
                ->whereYear("Date", "=", $request->startDate)->first();
            $totalRestPermits = PermitModel::selectRaw("SUM(used_day) as total_day,SUM(over_hour) as over_hour,SUM(over_minute) as over_minute")
                ->where(['Active' => 1, 'EmployeeID' => $EmployeeID, 'kind' => 14])->where('id', "<>", $request->permitId)
                ->whereYear("start_date", "=", $request->startDate)->first();

            $totalMinuteOfOvertimeRest = ($employeeOvertimeRestTotalHour->total_hour*60) + ($employeeOvertimeRestTotalHour->total_minute);
            $totalMinuteOfRestPermit = ($totalRestPermits->total_day*8*60) + ($totalRestPermits->over_hour*60) + ($totalRestPermits->over_minute) ;
            $requestedRestPermitMinute =  ($calculatePermit['UsedDay']*8*60) + ($calculatePermit['OverHour']*60) + ($calculatePermit['OverMinute']);

            if ($permitBeginDate->year !== $permitEndDate->year)
                return response([
                    'status' => false,
                    'message' => 'Dinlenme izni, farklı başlangıç yılı - farklı bitiş yılı şeklinde alınamaz.'
                ], 200);

            else if($totalMinuteOfRestPermit + $requestedRestPermitMinute > $totalMinuteOfOvertimeRest)
            {
                return response([
                    'status' => false,
                    'message' => "Dinlenme izni hakkınız kalmamıştır\nTalep Ettiğiniz İzin : " . $requestedPermit
                ], 200);
            }


            //Talep Edilen Gün ve kullanılan gün toplamı limiti geçiyor ise yani 14 günü geçiyor ise
            else if ($totalMinuteOfRestPermit + $requestedRestPermitMinute >= ($permitRest->dayLimitPerYear*8*60)) {
                return response([
                    'status' => false,
                    'message' => "Dinlenme izni hakkınız kalmamıştır\nTalep Ettiğiniz İzin : " . $requestedPermit
                ], 200);

            } //Talep edilen gün toplamı, kişinin hak ettiği izin günü sayısını geçiyor ise yani kişinin hak kazandığı dinlenme iznine göre yapılan hesap.



        }

        $permit = PermitModel::createPermit($request);
        if ($permit) {
            if ($request->permitId == null) {

                $userEmployee = EmployeeModel::find($request->Employee);
                $logStatus = LogsModel::setLog($request->Employee, $permit->id, 3, 15, '', '', 'İzin ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı çalışan tarafından oluşturuldu.', '', '', '', '', '');
            } else {
                $userEmployee = EmployeeModel::find($request->Employee);
                $logStatus = LogsModel::setLog($request->Employee, $permit->id, 3, 17, '', '', $permit->id . ' id nolu izin ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı çalışan tarafından düzenlendi.', '', '', '', '', '');
            }
            return response([
                'status' => true,
                'message' => "Kayıt Başarılı",
            ], 200);
        } else
            return response([
                'status' => false,
                'message' => "Kayıt Başarısız",
            ], 200);
    }


    /*public function savePermit2(Request $request)
    {
        $datetime1 = new DateTime($request->endDate);
        $datetime2 = new DateTime($request->startDate);
        $interval = $datetime2->diff($datetime1);
        $elapsed = $interval->format('%y years %m months %a days %h hours %i minutes %s seconds');
        $remainingDays = PermitModel::getRemainingDaysYearlyPermit($request);

        $permitStartDate = new DateTime($request->endDate);
        $permitEndDate = new DateTime($request->startDate);
        $interval = $permitEndDate->diff($permitStartDate);

        $requestedPermitDays =(int) $interval->format('%a');
        $requestedPermitHours =(int) $interval->format('%h');


        if ($requestedPermitDays > $remainingDays['daysLeft'])
        {
            return response([
                'status' => false,
                'message' => "Yıllık izin hakkınızdan fazla bir izin talep ettiniz.\n Kullandığınız izin miktarı : "
                    .$remainingDays['daysUsed'].' gün, '.$remainingDays['hoursUsed'].'saat.'.'\n Kalan İzin Miktarı : ' .$remainingDays['daysLeft'].' gün, '.$remainingDays['hoursLeft'].' saat.',
            ], 200);
        }
        else if ($requestedPermitHours > $remainingDays['hoursLeft'])
            return response([
                'status' => false,
                'message' => "Yıllık izin hakkınızdan fazla bir izin talep ettiniz.\n Kullandığınız izin miktarı : "
                    .$remainingDays['daysUsed'].' gün, '.$remainingDays['hoursUsed'].'saat.'.'\n Kalan İzin Miktarı : ' .$remainingDays['daysLeft'].' gün, '.$remainingDays['hoursLeft'].' saat.',
            ], 200);

        $status = PermitModel::createPermit($request);
        if ($status)
            return response([
                'status' => true,
                'message' => "Kayıt Başarılı",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "Kayıt Başarısız",
            ], 200);

    }*/

    public function permitPendingList(Request $request)
    {
        $status = ($request->status !== null) ? $request->status : null;
        $status = $status == "2" || $status == "3" || $status == "4" ? intval($status) : 1;

        $correction = $request->Correction;

        $ApprovalStatus = ($request->ApprovalStatus !== null) ? intval($request->ApprovalStatus) : null;
        if (($ApprovalStatus == 1 || $ApprovalStatus == 2) && $status <> 4) {
            $QueryStatus = $status + 1;
        } else
            $QueryStatus = $status;
        if ($status === null || $ApprovalStatus === null) {
            return response([
                'status' => false,
                'data' => "Eksik parametre",
            ], 200);
        }

        $permitQ = PermitModel::where(["active" => 1])->whereNotIn("netsis", [1]);
        if ($status == 1) {
            $usersApprove = EmployeePositionModel::where(["Active" => 2, "ManagerId" => $request->Employee])->pluck("EmployeeID");
            $permitQ->whereIn("EmployeeID", $usersApprove)->where(["status" => $QueryStatus, "manager_status" => $ApprovalStatus]);
            $correction == 'true' ? $permitQ->where(['correction_status' => 1]) : $permitQ->where(['correction_status' => 0]);
        } else if ($status == 2) {
            $hrRegion = ProcessesSettingsModel::where(["object_type" => 3, "PropertyCode" => "HRManager", "PropertyValue" => $request->Employee])->pluck("RegionID");
            $usersApprove = EmployeePositionModel::where(["Active" => 2])->whereIn("RegionID", $hrRegion)->groupBy("EmployeeID")->pluck("EmployeeID");
            $permitQ->whereIn("EmployeeID", $usersApprove)->where(["status" => $QueryStatus, "hr_status" => $ApprovalStatus]);
            $correction == 'true' ? $permitQ->where(['correction_status' => 1]) : $permitQ->where(['correction_status' => 0]);
        } else if ($status == 3) {
            $psRegion = ProcessesSettingsModel::where(["object_type" => 3, "PropertyCode" => "PersonnelSpecialist", "PropertyValue" => $request->Employee])->pluck("RegionID");
            $usersApprove = EmployeePositionModel::where(["Active" => 2])->whereIn("RegionID", $psRegion)->groupBy("EmployeeID")->pluck("EmployeeID");

            $permitQ->whereIn("EmployeeID", $usersApprove)->where(["status" => $QueryStatus, "ps_status" => $ApprovalStatus]);
        }
        $permitQ->orderBy("created_date", "DESC");
        $permits = $permitQ->get();

        return response([
            'status' => true,
            'data' => $permits,
        ], 200);
    }


    public function permitConfirm(Request $request)
    {
        $permitId = $request->permitId;
        if ($permitId === null) {
            return response([
                'status' => false,
                'message' => "İzin Id Boş Olamaz"
            ], 200);
        }
        $permit = PermitModel::find($permitId);
        if ($permit->correction_status == 1 && $permit->EmployeeID == $request->Employee)
        {
            $permit->correction_status = 0;
            $permit->save();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ],200);
        }
        $status = self::permitAuthority($permit, $request->Employee, "confirm");
        if ($status == false) {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if ($request->confirm == 1)
            $confirm = 1;
        else {
            $confirm = 2;
            $permit->netsis = 2;
        }

        if ($permit->status == 1) {
            $permit->manager_status = $confirm;

            NotificationsModel::saveNotification($permit->EmployeeID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebiniz, yöneticiniz tarafından onaylandı","my-permits/".$permit->id);
            $employee = EmployeeModel::find($permit->EmployeeID);
            $hrPersonnels = ProcessesSettingsModel::where(['RegionID' => $employee->EmployeePosition->RegionID,'object_type' => 3, 'PropertyCode' => 'HRManager'])->get();
            foreach ($hrPersonnels as $hrPersonnel)
                NotificationsModel::saveNotification($hrPersonnel->PropertyValue,3,$permit->id,$permit->PermitKind['name'],"İzin talebi için onayınız bekleniyor","permits/".$permit->id);
            if ($confirm == 2) {
                NotificationsModel::saveNotification($permit->EmployeeID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebiniz, yöneticiniz tarafından reddedildi","my-permits/".$permit->id);
                $permit->hr_status = 2;
                $permit->ps_status = 2;
            }
        } else if ($permit->status == 2) {
            $permit->hr_status = $confirm;
            NotificationsModel::saveNotification($permit->EmployeeID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebiniz, insan kaynakları birimi tarafından onaylandı, evrak onayı beklenmektedir","my-permits/".$permit->id);
            $employee = EmployeeModel::find($permit->EmployeeID);
            $personnelSpecialists = ProcessesSettingsModel::where(['RegionID' => $employee->EmployeePosition->RegionID,'object_type' => 3, 'PropertyCode' => 'PersonnelSpecialist'])->get();
            foreach ($personnelSpecialists as $personnelSpecialist)
                NotificationsModel::saveNotification($personnelSpecialist->PropertyValue,3,$permit->id,$permit->PermitKind['name'],"İzin talebi için onayınız bekleniyor","permits/".$permit->id);
            if ($confirm == 2) {
                $permit->ps_status = 2;
                NotificationsModel::saveNotification($permit->EmployeeID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebiniz, insan kaynakları birimi tarafından reddedildi","my-permits/".$permit->id);
            } else {
                PermitModel::createPermitDocumentAndSendMailToEmployee($request);
            }

        } else if ($permit->status == 3)
        {
            if ($confirm == 2)
                NotificationsModel::saveNotification($permit->EmployeeID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebinizin evrak onayı reddedildi","my-permits/".$permit->id);
            else
                NotificationsModel::saveNotification($permit->EmployeeID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebinizin evrakları onaylandı","my-permits/".$permit->id);

            $permit->ps_status = $confirm;
        }
        else if ($permit->status == 0)
        {
            $employee = EmployeeModel::find($permit->EmployeeID);
            NotificationsModel::saveNotification($employee->EmployeePosition->ManagerID,3,$permit->id,$permit->PermitKind['name'],$permit->PermitKind['name']." talebi için onayınız bekleniyor","permits/".$permit->id);
        }


        $permit->status = $permit->status + 1;
        $permitResult = $permit->save();
        if ($permitResult) {
            switch ($permit->status - 1) {
                case 1:
                    $userEmployee = EmployeeModel::find($request->Employee);
                    $logStatus = LogsModel::setLog($request->Employee, $permit->Id, 2, 18, '', '', 'İzin ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı yönetici tarafından onaylandı.', '', '', '', '', '');
                    break;
                case 2:
                    $userEmployee = EmployeeModel::find($request->Employee);
                    $logStatus = LogsModel::setLog($request->Employee, $permit->Id, 2, 19, '', '', 'İzin ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı insan kaynakları personeli tarafından onaylandı.', '', '', '', '', '');
                    break;
                case 3:
                    $userEmployee = EmployeeModel::find($request->Employee);
                    $logStatus = LogsModel::setLog($request->Employee, $permit->Id, 2, 20, '', '', 'İzin ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı evrak onay personeli tarafından onaylandı.', '', '', '', '', '');
                    break;
            }
            return response([
                'status' => true,
                'message' => "Onay İşlemi Başarılı"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Onaylama İşlemi Başarısız"
            ], 200);
        }

    }

    public function permitConfirmTakeBack(Request $request)
    {
        $permitId = $request->permitId;
        if ($permitId === null) {
            return response([
                'status' => false,
                'message' => "Belge Id Boş Olamaz"
            ], 200);
        }
        $permit = PermitModel::find($permitId);
        $status = self::permitAuthority($permit, $request->Employee, "takeBack");
        if ($status == false) {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if ($permit->netsis == 1) {
            return response([
                'status' => false,
                'message' => "Aktarımı Tamamlanmış İzinlerin Onayı Geri Alınamaz"
            ], 200);
        }

        if ($permit->status == 2) {
            $permit->manager_status = 0;
            $permit->hr_status = 0;
            $permit->ps_status = 0;
            $permit->netsis = 0;
            $permit->status = 1;
        } else if ($permit->status == 3) {
            $permit->hr_status = 0;
            $permit->ps_status = 0;
            $permit->netsis = 0;
            $permit->status = 2;
        } else if ($permit->status == 4) {
            $permit->ps_status = 0;
            $permit->netsis = 0;
            $permit->status = 3;
        }

        $permitResult = $permit->save();

        if ($permitResult) {
            return response([
                'status' => true,
                'message' => "Geri Alma Başarılı"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Geri Alma Başarısız"
            ], 200);
        }

    }

    public function permitAuthority($permit, $EmployeeID, $authType = "")
    {
        $status = false;
        if ($authType == "") return $status;

        $employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $permit->EmployeeID])->first();

        if (($permit->status == 1 && $authType == "takeBack") || ($permit->status == 0 && $authType == "confirm")) {
            if ($permit->EmployeeID == $EmployeeID)
                $status = true;
        } else if (($permit->status == 2 && $authType == "takeBack") || ($permit->status == 1 && $authType == "confirm")) {
            if ($employeePosition->ManagerID == $EmployeeID)
                $status = true;
        } else if (($permit->status == 3 && $authType == "takeBack") || ($permit->status == 2 && $authType == "confirm")) {
            $hrManager = ProcessesSettingsModel::where(["object_type" => 3, "PropertyCode" => "HRManager", "RegionId" => $employeePosition->RegionID])->first();
            if ($hrManager->PropertyValue == $EmployeeID && $hrManager->RegionID == $employeePosition->RegionID)
                $status = true;
        } else if (($permit->status == 4 && $authType == "takeBack") || ($permit->status == 3 && $authType == "confirm")) {
            $personnelSpecialist = ProcessesSettingsModel::where(["object_type" => 3, "PropertyCode" => "PersonnelSpecialist", "RegionId" => $employeePosition->RegionID])->first();
            if ($personnelSpecialist->PropertyValue == $EmployeeID && $personnelSpecialist->RegionID == $employeePosition->RegionID)
                $status = true;
        }

        return $status;
    }

    public function deletePermit(Request $request)
    {
        $permit = PermitModel::find($request->permitId);
        $permit->active = 0;
        if ($permit->save()) {
            $permit->fresh();
            $userEmployee = EmployeeModel::find($request->Employee);
            LogsModel::setLog($request->Employee, $permit->id, 3, 16, '', '', 'İzin ' . $userEmployee->UsageName . ' ' . $userEmployee->LastName . ' adlı çalışan tarafından silindi.', '', '', '', '', '');
            return response([
                'status' => true,
                'message' => 'Silme işlemi başarılı'
            ], 200);
        }

    }


}
