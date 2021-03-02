<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Library\Asay;
use App\Model\AdvancePaymentModel;
use App\Model\CompanyModel;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\LogsModel;
use App\Model\NotificationsModel;
use App\Model\ProcessesSettingsModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserHasGroupModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvancePaymentController extends ApiController
{

    public function save(Request $request)
    {
        $post["Name"]               = $request->input("Name");
        $post["Description"]        = $request->input("Description");
        $post["ExpenseType"]        = $request->input("ExpenseType");
        $post["ProjectId"]          = $request->input("ProjectId");
        $post["CategoryId"]         = $request->input("CategoryId")!==null ? $request->input("CategoryId") : "";
        $post["AdvancePaymentId"]   = $request->input("AdvancePaymentId")!==null ? $request->input("AdvancePaymentId") : "";;
        $post["RequirementDate"]    = $request->input("RequirementDate");
        $post["Amount"]             = $request->input("Amount");
        $post['Code']               = $request->input('Code');
        $post['TravelBy']           = $post['ExpenseType'] == 2 ? $request->input('TravelBy') : null;
        $post['TravelTo']           = $post['ExpenseType'] == 2 ? $request->input('TravelTo') : null;
        $post['TravelDate']         = $post['ExpenseType'] == 2 ? $request->input('TravelDate') : null;
        $post['TravelNight']        = $post['ExpenseType'] == 2 ? $request->input('TravelNight') :null;
        $post['TravelDay']          = $post['ExpenseType'] == 2 ? $request->input('TravelDay') :null;
        $post['TravelAccommodation']= $post['ExpenseType'] == 2 ? $request->input('TravelAccommodation') : null;
        $post['ToApproval']         = $request->input('ToApproval');
        $post['TakeBack']           = $request->input('TakeBack');
        $AdvancePaymentId           = $post["AdvancePaymentId"];

        if($AdvancePaymentId==""){
            $AdvancePayment = new AdvancePaymentModel();
            $AdvancePayment->Status = 0;
        }
        else
            $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);

        $AdvancePayment->Name                   = $post["Name"];
        $AdvancePayment->Code                   = $post['Code'];
        $AdvancePayment->Description            = $post["Description"];
        $AdvancePayment->EmployeeID             = $request->Employee;
        $AdvancePayment->ExpenseType            = $post["ExpenseType"];
        $AdvancePayment->ProjectId              = $post["ProjectId"]!==null ? $post["ProjectId"] : "";
        $AdvancePayment->CategoryId             = $post["CategoryId"]!==null ? $post["CategoryId"] : "";
        $AdvancePayment->RequirementDate        = $post["RequirementDate"];
        $AdvancePayment->Amount                 = $post["Amount"];
        $AdvancePayment->TravelBy               = $post['TravelBy'];
        $AdvancePayment->TravelTo               = $post['TravelTo'];
        $AdvancePayment->TravelDate             = $post['TravelDate'];
        $AdvancePayment->TravelDay              = $post['TravelDay'];
        $AdvancePayment->TravelNight            = $post['TravelNight'];
        $AdvancePayment->TravelAccommodation    = $post['TravelAccommodation'];

        if ($post['ToApproval'] != null)
        {
            $AdvancePayment->Status = 1;
            $mailData = ['allowance' => $AdvancePayment];
            $mailTable = view('mails.allowance', $mailData);
            $manager = DB::table("Employee")->where(['Active' => 1, 'Id' => $AdvancePayment->CreatedBy->EmployeePosition->ManagerID])->first();
            Asay::sendMail($manager->JobEmail, "", "Avans talebi için onayınız bekleniyor", $mailTable, "aSAY Group");
            NotificationsModel::saveNotification($manager->Id,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans talebi için onayınız bekleniyor.","");

        }
        $post['ToApproval'] != null ? $AdvancePayment->Status = 1 : '';
        $post['TakeBack'] != null ? $AdvancePayment->Status = 0 : '';

        if($AdvancePayment->save())
        {
            if($post["AdvancePaymentId"] == null || $post["AdvancePaymentId"] == "")
            {
                $AdvancePayment->fresh();
                $userEmployee = EmployeeModel::find($request->Employee);
                LogsModel::setLog($request->Employee,$AdvancePayment->Id,2,8,'','',$AdvancePayment->Name.' başlıklı avans '.$userEmployee->UsageName . '' . $userEmployee->LastName.' tarafından oluşturuldu.','','','','','');
            }

            else
            {
                //TODO Düzenlenen alanların tamamının loglanması gerekir.
                $AdvancePayment->fresh();
                $userEmployee = EmployeeModel::find($request->Employee);
                LogsModel::setLog($request->Employee,$AdvancePayment->Id,2,10,'','',$AdvancePayment->Name.' başlıklı avans '.$userEmployee->UsageName . '' . $userEmployee->LastName.' tarafından düzenlendi.','','','','','');
            }

            return response([
                'status' => true,
                'data' => [
                    "AdvancePaymentId"=>$AdvancePayment->id
                ]
            ], 200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => "Kayıt Yapılırken Hata Oluştu",
            ], 200);
        }
    }

    public function list2(Request $request)
    {
        $status = $request->status != null ? $request->status : 0;
        $approveStatus = $request->approveStatus != null ? $request->approveStatus : '';

        $allowanceQ = AdvancePaymentModel::where(['EmployeeID' => $request->Employee,'Active' => 1]);

        $queryColumn = '';

        switch ($status)
        {
            case 1:
                $queryColumn = 'ManagerStatus';
                break;
            case 2:
                $queryColumn = 'PmStatus';
                break;
            case 3:
                $queryColumn = 'AccountingStatus';
                break;
        }

        if ($approveStatus == 1)
        {
            $status++;
        }

        $allowanceQ->where(['Status' => $status]);

        if ($queryColumn != '')
            $allowanceQ->where([$queryColumn => $approveStatus]);

        $list = $allowanceQ->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $list,
            'status' => $status,
            'column' => $queryColumn
        ],200);

    }

    public function list(Request $request)
    {
        $error = false;
        $in_status = ["0","1","2","3","4"];
        $status = ($request->status!==null) ? $request->status : "";
        if(!in_array($status,$in_status)){
            return response([
                'status' => false,
                'message' => "Eksik yada Yanlış Parametre",
            ], 200);
        }

        $employeeManagers = EmployeePositionModel::where(["Active"=>2,"ManagerId"=>$request->Employee])->pluck("EmployeeID");
        $projects   = ProjectsModel::where(["manager_id"=>$request->Employee])->pluck("id");
        $categories = ProjectCategoriesModel::where(["manager_id"=>$request->Employee])->pluck("id");

        $advanceQ = AdvancePaymentModel::where(["AdvancePayment.Active"=>1]);

        $advanceQ->where(function($query) use($projects,$categories,$employeeManagers,$status){
            if($status==2){
                $query->whereIn("ProjectId",$projects);
                $query->whereIn("CategoryId",$categories,"OR");
            }

            if($status==1)
                $query->whereIn("EmployeeID",$employeeManagers,"OR");

        });
        if($status==0){
            $statusArray = [0,1,2,3,4];
            $advanceQ->where(["EmployeeID"=>$request->Employee]);
        }
        else if($status==3 || $status==4)
        {
            //TODO İk Özlük Bilgilerindeki Erişim Türüne Bağlı Gelecektir.
            $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID" => $request->Employee, "group_id" => 12, 'active' => 1])->count();
            $processSettingAllowanceAccounter = ProcessesSettingsModel::where(['object_type' => 2,'PropertyValue' => $request->Employee,'PropertyCode' => 'Accounter'])->count();
            if(($userGroupCount>0 || $processSettingAllowanceAccounter > 0) && ($status==3 || $status==4))
            {
                if ($status == 3)
                {
                    $statusArray = [3];
                    if ($request->rejectedStatus == 1)
                    {
                        $advanceQ->where(['AccountingStatus' => 2]);
                    }
                    else
                    {
                        $advanceQ->where(['AccountingStatus' => 0]);
                    }
                }
                else
                {
                    $statusArray = [4];
                    $advanceQ->where(['AccountingStatus' => 1]);
                }
            }
            else{
                $error = true;
            }
        }
        else if($status==2 && $request->rejectedStatus == 1)
        {
            $statusArray = [2];
            $advanceQ->where(['PmStatus' => 2]);
        }
        else if($status==2 && $request->singleStatus == 1)
        {
            $advanceQ->where(['PmStatus' => 0]);
            $statusArray = [2];
        }
        else if($status==2 && $request->singleStatus == 0)
        {
            $statusArray = [3,4];
            $advanceQ->where(['PmStatus' => 1]);
        }
        else if($status==1 && $request->rejectedStatus == 1)
        {
            $statusArray = [1];
            $advanceQ->where(['ManagerStatus' => 2]);
        }
        else if($status==1 && $request->singleStatus == 1)
        {
            $advanceQ->where(['ManagerStatus' => 0]);
            $statusArray = [1];
        }
        else if($status==1 && $request->singleStatus == 0)
        {
            $statusArray = [2,3,4];
            $advanceQ->where(['ManagerStatus' => 1]);
        }



        if($error==true){
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem",
            ], 200);
        }


        $advanceQ->whereIn("AdvancePayment.Status",$statusArray);
        $advanceQ->orderBy("AdvancePayment.CreatedDate","DESC");
        $data["expenses"] = $advanceQ->get();

        return response([
            'status' => true,
            'data' => $data
        ], 200);
    }

    public function getAdvance(Request $request)
    {
        $AdvancePaymentId   = $request->AdvancePaymentId;
        $AdvancePayment     = AdvancePaymentModel::find($AdvancePaymentId);

        if($AdvancePayment===null)
        {
            return response([
                'status' => false,
                'message' => "Avans bulunamadı"
            ], 200);
        }
        else if($AdvancePayment->EmployeeID==$request->Employee)
        {
            return response([
                'status' => true,
                'data' => $AdvancePayment
            ], 200);
        }
        else
        {
            $status = self::advanceAuthority($AdvancePayment,$request->Employee);
            if($status==false)
            {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz İşlem"
                ], 200);
            }
            else
            {
                return response([
                    'status' => true,
                    'data' => $AdvancePayment
                ], 200);
            }
        }
    }

    public function confirmTakeBack(Request $request)
    {
        $AdvancePaymentId   = $request->AdvancePaymentId;
        if($AdvancePaymentId===null)
        {
            return response([
                'status' => false,
                'message' => "Avans Numarası Boş Olamaz"
            ], 200);
        }
        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);

        $status = self::advanceAuthority($AdvancePayment,$request->Employee,"takeBack");
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if($AdvancePayment->Status==1){
            $AdvancePayment->ManagerStatus     = 0;
            $AdvancePayment->PmStatus          = 0;
            $AdvancePayment->AccountingStatus  = 0;
            $AdvancePayment->Netsis=0;
        }
        else if($AdvancePayment->Status==2){
            $AdvancePayment->ManagerStatus     = 0;
            $AdvancePayment->PmStatus          = 0;
            $AdvancePayment->AccountingStatus  = 0;
            $AdvancePayment->Netsis=0;
        }
        else if($AdvancePayment->Status==3){
            $AdvancePayment->PmStatus          = 0;
            $AdvancePayment->AccountingStatus  = 0;
            $AdvancePayment->Netsis=0;
        }
        else{
            $AdvancePayment->AccountingStatus  = 0;
        }
        $AdvancePayment->Status = $AdvancePayment->Status - 1;
        $AdvancePaymentResult = $AdvancePayment->save();

        if($AdvancePaymentResult){
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

    public function confirm(Request $request)
    {
        $AdvancePaymentId   = $request->AdvancePaymentId;
        if($AdvancePaymentId===null)
        {
            return response([
                'status' => false,
                'message' => "Avans Numarası Boş Olamaz"
            ], 200);
        }
        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);
        $status = self::advanceAuthority($AdvancePayment,$request->Employee,"confirm");
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if($request->Confirm==1)
            $confirm = 1;
        else{
            $confirm = 2;
            $AdvancePayment->Netsis = 2;
        }

        $mailData = ['allowance' => $AdvancePayment];
        $mailTable = view('mails.allowance', $mailData);

        if($AdvancePayment->Status==1)
        {

            if($AdvancePayment->CategoryId != null)
            {
                $projectManagerID = ProjectCategoriesModel::find($AdvancePayment->CategoryId)->manager_id;
                $projectManager = DB::table("Employee")->where(['Active' => 1,'Id' => $projectManagerID])->first();
            }
            else{
                $projectManagerID = ProjectsModel::find($AdvancePayment->ProjectId)->manager_id;
                $projectManager = DB::table("Employee")->where(['Active' => 1,'Id' => $projectManagerID])->first();
            }
            $AdvancePayment->ManagerStatus = $confirm;
            if($confirm==2){
                $AdvancePayment->AccountingStatus = 2;
                $AdvancePayment->PmStatus = 2;

                Asay::sendMail($AdvancePayment->CreatedBy->JobEmail, "", "Avans, yöneticiniz tarafından reddedildi", $mailTable, "aSAY Group");
                NotificationsModel::saveNotification($AdvancePayment->EmployeeID,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans, yöneticiniz tarafından reddedildi.","");
            }
            else{
                Asay::sendMail($AdvancePayment->CreatedBy->JobEmail, "", "Avans, yöneticiniz tarafından onaylandı", $mailTable, "aSAY Group");
                Asay::sendMail($projectManager->JobEmail, "", "Avans talebi için onayınız bekleniyor", $mailTable, "aSAY Group");
                NotificationsModel::saveNotification($AdvancePayment->EmployeeID,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans, yöneticiniz tarafından onaylandı.","");
                NotificationsModel::saveNotification($projectManager->Id,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans talebi için onayınız bekleniyor.","");
            }
        }
        else if($AdvancePayment->Status==2){
            $AdvancePayment->PmStatus = $confirm;
            if($confirm==2){
                $AdvancePayment->AccountingStatus = 2;
                Asay::sendMail($AdvancePayment->CreatedBy->JobEmail, "", "Avans, proje yöneticisi tarafından reddedildi", $mailTable, "aSAY Group");
                NotificationsModel::saveNotification($AdvancePayment->EmployeeID,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans, proje yöneticisi tarafından reddedildi.","");
            }
            else{
                $accounters = ProcessesSettingsModel::where(['object_type' => 2, 'PropertyCode' => 'Accounter'])->groupBy("PropertyValue")->pluck('PropertyValue');
                $accounterEmployees = DB::table("Employee")->whereIn("Id",$accounters)->get();
                foreach ($accounterEmployees as $accounterEmployee)
                {
                    Asay::sendMail($accounterEmployee->JobEmail, "", "Avans talebi için onayınız bekleniyor", $mailTable, "aSAY Group");
                    NotificationsModel::saveNotification($accounterEmployee->Id,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans talebi için onayınız bekleniyor.","");
                }
                Asay::sendMail($AdvancePayment->CreatedBy->JobEmail, "", "Avans, proje yöneticisi tarafından onaylandı", $mailTable, "aSAY Group");
                NotificationsModel::saveNotification($AdvancePayment->EmployeeID,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans, proje yöneticisi tarafından onaylandı.","");
            }
        }
        else if($AdvancePayment->Status==3)
        {
            $AdvancePayment->AccountingStatus = $confirm;
            if($confirm != 2)
            {
                Asay::sendMail($AdvancePayment->CreatedBy->JobEmail, "", "Avans, muhasebe birimi tarafından onaylandı", $mailTable, "aSAY Group");
                NotificationsModel::saveNotification($AdvancePayment->EmployeeID,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans, muhasebe birimi tarafından onaylandı.","");
            }
            else{
                Asay::sendMail($AdvancePayment->CreatedBy->JobEmail, "", "Avans, muhasebe birimi tarafından reddedildi", $mailTable, "aSAY Group");
                NotificationsModel::saveNotification($AdvancePayment->EmployeeID,2,$AdvancePayment->id,$AdvancePayment->Name,"Avans, muhasebe birimi tarafından reddedildi.","");
            }
        }

        //TODO Netsise aktardıktan sonra status 4 mü olacak ?
        //TODO Netsise Aktarım eklenecek
        $AdvancePayment->Status = $confirm == 2 ? $AdvancePayment->Status :$AdvancePayment->Status + 1;
        $AdvancePaymentResult = $AdvancePayment->save();
        if($AdvancePaymentResult){
            switch ($AdvancePayment->Status - 1)
            {
                case 1:
                    $userEmployee = EmployeeModel::find($request->Employee);
                    LogsModel::setLog($request->Employee,$AdvancePayment->Id,2,11,'','',$AdvancePayment->Name.' başlıklı avans '.$userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından onaylandı.','','','','','');
                    break;
                case 2:
                    $userEmployee = EmployeeModel::find($request->Employee);
                    LogsModel::setLog($request->Employee,$AdvancePayment->Id,2,12,'','',$AdvancePayment->Name.' başlıklı avans '.$userEmployee->UsageName . '' . $userEmployee->LastName.' adlı proje yöneticisi tarafından onaylandı.','','','','','');
                    break;
                case 3:
                    $userEmployee = EmployeeModel::find($request->Employee);
                    LogsModel::setLog($request->Employee,$AdvancePayment->Id,2,13,'','',$AdvancePayment->Name.' başlıklı avans '.$userEmployee->UsageName . '' . $userEmployee->LastName.' adlı muhasebe yöneticisi tarafından onaylandı.','','','','','');
                    break;
            }
            return response([
                'status' => true,
                'message' => "Onay Başarılı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Onaylama Başarısız"
            ], 200);
        }

    }

    public function complete(Request $request){

        $AdvancePaymentId   = $request->AdvancePaymentId;
        if($AdvancePaymentId===null)
        {
            return response([
                'status' => false,
                'message' => "Avans Numarası Boş Olamaz"
            ], 200);
        }
        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);
        $AdvancePayment->Status = $AdvancePayment->Status +1;

        $AdvancePaymentResult = $AdvancePayment->save();
        if($AdvancePaymentResult){
            return response([
                'status' => true,
                'message' => "Onay Başarılı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Onaylama Başarısız"
            ], 200);
        }

    }

    public function deleteAdvance(Request $request)
    {
        $AdvancePaymentId = $request->AdvancePaymentId;
        if($AdvancePaymentId===null)
        {
            return response([
                'status' => false,
                'message' => "Avasns Id Boş Olamaz"
            ], 200);
        }

        $userEmployee = EmployeeModel::find($request->Employee);

        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);
        if($AdvancePayment->EmployeeID!=$request->Employee)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
        $AdvancePayment->active=0;
        $AdvancePaymentResult = $AdvancePayment->save();
        if($AdvancePaymentResult){
            LogsModel::setLog($request->Employee,$AdvancePayment->Id,2,9,'','',$AdvancePayment->Name.' başlıklı avans '.$userEmployee->UsageName . '' . $userEmployee->LastName.' tarafından silindi.','','','','','');
            return response([
                'status' => true,
                'message' => "Avans Silme Başarılı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Silme Başarısız"
            ], 200);
        }
    }

    public function advanceAuthority($advancePayment,$EmployeeID, $authType = "")
    {
        $status = false;

        if ($authType == "") return $status;

        if ( ($advancePayment->Status == 0 && $authType = 'confirm') || ($advancePayment->Status == 1 && $authType == "takeBack") )
        {
            $status = $advancePayment->EmployeeID == $EmployeeID ? true : false;
        }

        if( ($advancePayment->Status == 1 && $authType == "confirm") || ($advancePayment->Status == 2 && $authType == "takeBack") )
        {
            $employeePosition = EmployeePositionModel::where(["Active"=>2,"EmployeeID"=>$advancePayment->EmployeeID])->first();
            if($employeePosition->ManagerID==$EmployeeID)
                $status = true;
        }
        else if( ($advancePayment->Status==2 && $authType == "confirm") || ($advancePayment->Status==3 && $authType == "takeBack") ) {
            if($advancePayment->CategoryId<>""){
                $projetCategories = ProjectCategoriesModel::find($advancePayment->CategoryId);
                if($EmployeeID==$projetCategories->manager_id)
                    $status = true;
            }
            else {
                $project = ProjectsModel::find($advancePayment->ProjectId);
                if($EmployeeID==$project->manager_id)
                    $status = true;
            }

        }
        else if( ($advancePayment->Status==3 && $authType == "confirm") || ($advancePayment->Status==4 && $authType == "takeBack") || ($advancePayment->Status==4 && $authType == "netsis")  ) {
            $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID"=>$EmployeeID, "group_id"=>12, 'active' => 1])->count();
            $processSettingAllowanceAccounter = ProcessesSettingsModel::where(['object_type' => 2,'PropertyValue' => $EmployeeID,'PropertyCode' => 'Accounter'])->count();
            if($userGroupCount>0 || $processSettingAllowanceAccounter > 0)
                $status = true;
            if ($authType == "takeBack")
                $status = true;
        }
        return $status;
    }


    public function SendNetsis(Request $request)
    {
        $AdvancePaymentId = $request->AdvancePaymentId;

        //MASRAF DETAYLARI
        $advancePayment = AdvancePaymentModel::find($AdvancePaymentId);
        if($advancePayment->Netsis==1 && $advancePayment->AccountingStatus==0){
            return response([
                'status' => false,
                'message' => "Aktarımı Tamamlanmış Yada Onaylanmamış Kayıtlar Gönderilemez"
            ], 200);
        }
        $status = self::advanceAuthority($advancePayment,$request->Employee,"netsis");
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        //Personel bilgileri kontrol ediliyor.
        $employee = EmployeeModel::find($advancePayment->EmployeeID);
        $employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $advancePayment->EmployeeID])->first();
        //$employee = EmployeeModel::find($request->Employee);
        //$employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $request->Employee])->first();
        $company = CompanyModel::find($employeePosition->CompanyID);
        $companyCode = $company->NetsisName;


        if ($advancePayment->ExpenseType == 1)//İş Avansı
        {
            $Query = "CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%'";
        }
        elseif ($advancePayment->ExpenseType == 2)//Seyahat Avansı
        {
            $Query = "CARI_KOD LIKE 'PS%'";
        }

        //CARİ KOD ÖĞREN
        $PersonelCariKodu = "";
        $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE " . $Query . " and EMAIL= :email", ["email" => $employee->JobEmail]);
        if (count($CariKod) > 0)
            $PersonelCariKodu = $CariKod[0]->CARI_KOD;


        if ($PersonelCariKodu == "") {
            $Log = "Personel Cari Kodu Netsisde Bulunamadı. Mail adresini kontrol ediniz.<br>Mail Adresi:" . $employee->JobEmail;
            return response([
                'status' => false,
                'message' => $Log
            ], 200);
        }

        //$bankCode = ProcessesSettingsModel::where(["object_type"=>2,"Property"=>$companyCode,"PropertyCode"=>"BankCode"])->first()->PropertyValue;

        $tarih = date("Y-m-d");
        $AvansOdeme = new \stdClass();
        $AvansOdeme->Tarih      = new Carbon($tarih);
        $AvansOdeme->CariKod    = $PersonelCariKodu;
        //$AvansOdeme->BankaKodu  = $bankCode;
        $AvansOdeme->Aciklama   = $advancePayment->Description;
        $AvansOdeme->Tutar      = $advancePayment->Amount;

        $wsdl    = 'http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?wsdl';

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $options = array(
            'uri'               =>'http://schemas.xmlsoap.org/wsdl/soap/',
            'style'             =>SOAP_RPC,
            'use'               =>SOAP_ENCODED,
            'soap_version'      =>SOAP_1_1,
            'cache_wsdl'        =>WSDL_CACHE_NONE,
            'connection_timeout'=>15,
            'trace'             =>true,
            'encoding'          =>'UTF-8',
            'exceptions'        =>true,
            "location" => "http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?singleWsdl",
        );
        try
        {
            $dbA = date("Y", strtotime($tarih));
            $soap = new \SoapClient($wsdl, $options);
            $data = $soap->AvansOdemeKaydet(array( "_IsletmeKodu" => $companyCode, "_AvansOdeme" => $AvansOdeme, "SirketAdi" => "ASAYGROUP" . $dbA));
            exit;
            if ($data->AvansOdemeKaydetResult->Sonuc == 1) {
                $advancePayment->Netsis = 1;
                $advancePayment->save();
                return response([
                    'status' => true,
                    'data' => "Avans Netsise Aktarıldı"
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'data' => "Hata Oluştu"
                ], 200);
            }
            //TODO: log yazılacak
        }
        catch(Exception $e)
        {
            return response([
                'status' => false,
                'data' => "Hata Oluştu"
            ], 200);
            //TODO: log yazılacak
        }

    }


    public function BankCodes(Request $request)
    {
        $wsdl    = 'http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?wsdl';

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $options = array(
            'uri'               =>'http://schemas.xmlsoap.org/wsdl/soap/',
            'style'             =>SOAP_RPC,
            'use'               =>SOAP_ENCODED,
            'soap_version'      =>SOAP_1_1,
            'cache_wsdl'        =>WSDL_CACHE_NONE,
            'connection_timeout'=>15,
            'trace'             =>true,
            'encoding'          =>'UTF-8',
            'exceptions'        =>true,
            "location" => "http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?singleWsdl",
        );

        $soap = new \SoapClient($wsdl, $options);
        $data = $soap->BankaTanimListesi(["_IsletmeKodu" => "Asay_Iletisim"]);

        return response([
            'status' => false,
            'data' => $data->BankaTanimListesiResult
        ], 200);
    }
}
