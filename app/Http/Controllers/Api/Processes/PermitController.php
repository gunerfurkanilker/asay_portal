<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\PermitKindModel;
use App\Model\PermitLeftOverHoursModel;
use App\Model\PermitModel;
use App\Model\ProcessesSettingsModel;
use App\Model\PublicHolidayModel;
use App\Model\UserModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
use Exception;
use SoapClient;

class PermitController extends ApiController
{
    public function permitList(Request $request)
    {
        $employee = EmployeeModel::find( UserModel::find($request->userId)->EmployeeID );
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => PermitModel::where('EmployeeID',$employee->Id)->get()
        ], 200);
    }

    public function getPermit(Request $request)
    {
        $employee = EmployeeModel::find( UserModel::find($request->userId)->EmployeeID );
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => PermitModel::where(["id"=>$request->permitId,'EmployeeID'=>$employee->Id])->first()
        ], 200);
    }

    public function permitTypes(Request $request)
    {
        $permitKinds = PermitKindModel::where(["active"=>1])->get();
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => $permitKinds
        ], 200);
    }

    public function savePermit(Request $request)
    {
        if($request->permitId!==null){
            $EmployeeID = PermitModel::find($request->permitId)->EmployeeID;
        }
        else
            $EmployeeID = UserModel::find($request->userId)->EmployeeID;
        //tanımlar
        $endDate = $request->endDate;
        $startDate = $request->startDate;

        if($startDate>$endDate)
        {
            return response([
                'status' => false,
                'message' => "İzin Tarihlerini Kontrol Ediniz. Hatalı Girdiniz."
            ], 200);
        }

        $calculatePermit = PermitModel::calculatePermit($startDate,$endDate);

        if($request->kind==12){
            //Yıllık İzin ise
            $remainingDays = PermitModel::netsisRemainingPermit($EmployeeID);
            if (($calculatePermit["UsedDay"] > $remainingDays['daysLeft']) ||
                ($calculatePermit["UsedDay"] == $remainingDays['daysLeft'] && $calculatePermit["OverHour"] > $remainingDays['hoursLeft']))
            {
                return response([
                    'status' => false,
                    'message' => "Yıllık izin hakkınızdan fazla bir izin talep ettiniz.\n Kullandığınız izin miktarı : "
                        .$calculatePermit["UsedDay"].' gün, '.$calculatePermit["OverHour"].'saat.'.'\n Kalan İzin Miktarı : ' .$remainingDays['daysLeft'].' gün, '.$remainingDays['hoursLeft'].' saat.',
                ], 200);
            }
        }
        else{
            //Yıllık izin haricindeki tipler kullanım kontrolü
        }

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
        $status = ($request->status!==null) ? $request->status : "";
        $status = $status=="2" || $status=="3" || $status=="4" ? intval($status) : 1;

        $ApprovalStatus = ($request->ApprovalStatus!==null) ? intval($request->ApprovalStatus) : "";
        if(($ApprovalStatus==1 || $ApprovalStatus==2) && $status<>4){
            $QueryStatus = $status+1;
        }
        else
            $QueryStatus = $status;
        if($status=="" || $ApprovalStatus==""){
            return response([
                'status' => false,
                'data' => "Eksik parametre",
            ], 200);
        }

        $user = UserModel::find($request->userId);
        $permitQ = PermitModel::where(["active"=>1,"netsis"=>0]);
        if($status==1){
            $usersApprove   = EmployeePositionModel::where(["Active"=>2,"ManagerId"=>$user->EmployeeID])->pluck("EmployeeID");

            $permitQ->whereIn("EmployeeID",$usersApprove)->where(["status"=>$QueryStatus,"manager_status"=>$ApprovalStatus]);
        }
        else if($status==2){
            $hrRegion   = ProcessesSettingsModel::where(["object_type"=>3,"PropertyCode"=>"HRManager","PropertyValue"=>$user->EmployeeID])->pluck("RegionID");
            $usersApprove = EmployeePositionModel::where(["Active"=>2])->whereIn("RegionID",$hrRegion)->groupBy("EmployeeID")->pluck("EmployeeID");

            $permitQ->whereIn("EmployeeID",$usersApprove)->where(["status"=>$QueryStatus,"hr_status"=>$ApprovalStatus]);
        }
        else if($status==3){
            $psRegion   = ProcessesSettingsModel::where(["object_type"=>3,"PropertyCode"=>"PersonnelSpecialist","PropertyValue"=>$user->EmployeeID])->pluck("RegionID");
            $usersApprove    = EmployeePositionModel::where(["Active"=>2])->whereIn("RegionID",$psRegion)->groupBy("EmployeeID")->pluck("EmployeeID");

            $permitQ->whereIn("EmployeeID",$usersApprove)->where(["status"=>$QueryStatus,"ps_status"=>$ApprovalStatus]);
        }
        $permitQ->orderBy("created_date","DESC");
        $permits = $permitQ->get();

        return response([
            'status' => true,
            'data' => $permits,
        ], 200);
    }


    public function permitConfirm(Request $request)
    {
        $user_id = $request->userId;
        $permitId = $request->permitId;
        if($permitId===null)
        {
            return response([
                'status' => false,
                'message' => "İzin Id Boş Olamaz"
            ], 200);
        }
        $permit = PermitModel::find($permitId);
        $status = self::permitAuthority($permit,$user_id,"confirm");
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if($request->confirm==1)
            $confirm = 1;
        else{
            $confirm = 2;
            $permit->netsis = 2;
        }

        if($permit->status==1)
        {
            $permit->manager_status = $confirm;
            if($confirm==2){
                $permit->hr_status = 2;
                $permit->ps_status = 2;
            }
        }
        else if($permit->status==2){
            $permit->hr_status = $confirm;
            if($confirm==2){
                $permit->ps_status = 2;
            }
        }
        else if($permit->status==3)
            $permit->ps_status = $confirm;

        $permit->status = $permit->status+1;
        $permitResult = $permit->save();
        if($permitResult){
            return response([
                'status' => true,
                'message' => "Onay İşlemi Başarılı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Onaylama İşlemi Başarısız"
            ], 200);
        }

    }

    public function permitConfirmTakeBack(Request $request)
    {
        $user_id = $request->userId;
        $permitId = $request->permitId;
        if($permitId===null)
        {
            return response([
                'status' => false,
                'message' => "Belge Id Boş Olamaz"
            ], 200);
        }
        $permit = PermitModel::find($permitId);
        $status = self::permitAuthority($permit,$user_id,"takeBack");
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if($permit->netsis==1){
            return response([
                'status' => false,
                'message' => "Aktarımı Tamamlanmış İzinlerin Onayı Geri Alınamaz"
            ], 200);
        }

        if($permit->status==2){
            $permit->manager_status     = 0;
            $permit->hr_status          = 0;
            $permit->ps_status          = 0;
            $permit->netsis             = 0;
            $permit->status             = 1;
        }
        else if($permit->status==3){
            $permit->hr_status  = 0;
            $permit->ps_status  = 0;
            $permit->netsis     = 0;
            $permit->status     = 2;
        }
        else if($permit->status==4 && $permit->netsis==0){
            $permit->ps_status  = 0;
            $permit->netsis     = 0;
            $permit->status     = 3;
        }
        $permitResult = $permit->save();

        if($permitResult){
            return response([
                'status' => true,
                'message' => "Geri Alma Başarılı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Geri Alma Başarısız"
            ], 200);
        }

    }

    public function permitAuthority($permit,$user_id,$authType="")
    {
        $status = false;
        if($authType=="") return $status;

        $user = UserModel::find($user_id);
        $employeePosition = EmployeePositionModel::where(["Active"=>2,"EmployeeID"=>$permit->EmployeeID])->first();

        if(($permit->status==1 && $authType=="takeBack") || ($permit->status==0 && $authType=="confirm")){
            if($permit->EmployeeID==$user->EmployeeID)
                $status = true;
        }
        else if(($permit->status==2 && $authType=="takeBack") || ($permit->status==1 && $authType=="confirm")){
            if($employeePosition->ManagerID==$user->EmployeeID)
                $status = true;
        }
        else if(($permit->status==3 && $authType=="takeBack") || ($permit->status==2 && $authType=="confirm")){
            $hrManager   = ProcessesSettingsModel::where(["object_type"=>3,"PropertyCode"=>"HRManager"])->first();
            if($hrManager->PropertyValue==$user->EmployeeID && $hrManager->RegionID==$employeePosition->RegionID)
                $status = true;
        }
        else if(($permit->status==4 && $authType=="takeBack") || ($permit->status==3 && $authType=="confirm")){
            $personnelSpecialist= ProcessesSettingsModel::where(["object_type"=>3,"PropertyCode"=>"PersonnelSpecialist"])->first();
            if($personnelSpecialist->PropertyValue==$user->EmployeeID && $personnelSpecialist->RegionID==$employeePosition->RegionID)
                $status = true;
        }

        return $status;
    }




}
