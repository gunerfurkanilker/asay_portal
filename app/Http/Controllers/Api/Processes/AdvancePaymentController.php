<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Model\AdvancePaymentModel;
use App\Model\EmployeePositionModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserHasGroupModel;
use App\Model\UserModel;
use Illuminate\Http\Request;

class AdvancePaymentController extends ApiController
{

    public function save(Request $request)
    {
        $user = UserModel::find($request->userId);
        $post["Name"]               = $request->input("Name");
        $post["Description"]        = $request->input("Description");
        $post["ExpenseType"]        = $request->input("ExpenseType");
        $post["ProjectId"]          = $request->input("ProjectId");
        $post["CategoryId"]         = $request->input("CategoryId")!==null ? $request->input("CategoryId") : "";
        $post["AdvancePaymentId"]   = $request->input("AdvancePaymentId")!==null ? $request->input("AdvancePaymentId") : "";;
        $post["RequirementDate"]    = $request->input("RequirementDate");
        $post["Amount"]             = $request->input("Amount");
        $AdvancePaymentId           = $post["AdvancePaymentId"];

        if($AdvancePaymentId==""){
            $AdvancePayment = new AdvancePaymentModel();
            $AdvancePayment->Status = 0;
        }
        else
            $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);

        $AdvancePayment->Name              = $post["Name"];
        $AdvancePayment->Description       = $post["Description"];
        $AdvancePayment->EmployeeID        = $user->EmployeeID;
        $AdvancePayment->ExpenseType       = $post["ExpenseType"];
        $AdvancePayment->ProjectId         = $post["ProjectId"]!==null ? $post["ProjectId"] : "";
        $AdvancePayment->CategoryId        = $post["CategoryId"]!==null ? $post["CategoryId"] : "";
        $AdvancePayment->RequirementDate   = $post["RequirementDate"];
        $AdvancePayment->Amount            = $post["Amount"];

        if($AdvancePayment->save())
        {
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

        $user = UserModel::find($request->userId);
        $employeeManagers = EmployeePositionModel::where(["Active"=>2,"ManagerId"=>$user->EmployeeID])->pluck("EmployeeID");
        $projects   = ProjectsModel::where(["manager_id"=>$user->EmployeeID])->pluck("id");
        $categories = ProjectCategoriesModel::where(["manager_id"=>$user->EmployeeID])->pluck("id");

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
            $advanceQ->where(["EmployeeID"=>$user->EmployeeID]);
        }
        else if($status==3 || $status==4)
        {
            $userGroupCount = UserHasGroupModel::where(["user_id"=>$request->userId,"group_id"=>58])->count();
            if($userGroupCount>0 && ($status==3 || $status==4))
                $statusArray[] = $status;
            else{
                $error = true;
            }
        }
        else
            $statusArray[] = $status;

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
        $user               = UserModel::find($request->userId);
        $AdvancePaymentId   = $request->AdvancePaymentId;
        $AdvancePayment     = AdvancePaymentModel::find($AdvancePaymentId);

        if($AdvancePayment===null)
        {
            return response([
                'status' => false,
                'message' => "Avans bulunamadı"
            ], 200);
        }
        else if($AdvancePayment->EmployeeID==$user->EmployeeID)
        {
            return response([
                'status' => true,
                'data' => $AdvancePayment
            ], 200);
        }
        else
        {
            $status = self::advanceAuthority($AdvancePayment,$user->id);
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
        $user_id            = $request->userId;
        $AdvancePaymentId   = $request->AdvancePaymentId;
        if($AdvancePaymentId===null)
        {
            return response([
                'status' => false,
                'message' => "Avans Numarası Boş Olamaz"
            ], 200);
        }
        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);

        $status = self::expenseAuthority($AdvancePayment,$user_id);
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
            $AdvancePayment->PmStatus          = 0;
            $AdvancePayment->AccountingStatus  = 0;
            $AdvancePayment->Netsis=0;
        }
        else if($AdvancePayment->Status==3){
            $AdvancePayment->AccountingStatus  = 0;
            $AdvancePayment->Netsis=0;
        }
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
        $user_id            = $request->userId;
        $AdvancePaymentId   = $request->AdvancePaymentId;
        if($AdvancePaymentId===null)
        {
            return response([
                'status' => false,
                'message' => "Avans Numarası Boş Olamaz"
            ], 200);
        }
        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);
        $status = self::expenseAuthority($AdvancePayment,$user_id);
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

        if($AdvancePayment->Status==1)
        {
            $AdvancePayment->ManagerStatus = $confirm;
            if($confirm==2){
                $AdvancePayment->AccountingStatus = 2;
                $AdvancePayment->PmStatus = 2;
            }
        }
        else if($AdvancePayment->Status==2){
            $AdvancePayment->PmStatus = $confirm;
            if($confirm==2){
                $AdvancePayment->AccountingStatus = 2;
            }
        }
        else if($AdvancePayment->Status==3)
            $AdvancePayment->AccountingStatus = $confirm;

        //TODO Netsise Aktarım eklenecek

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

        $user = UserModel::find($request->userId);

        $AdvancePayment = AdvancePaymentModel::find($AdvancePaymentId);
        if($AdvancePayment->EmployeeID!=$user->EmployeeID)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
        $AdvancePayment->active=0;
        $AdvancePaymentResult = $AdvancePayment->save();
        if($AdvancePaymentResult){
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

    public function advanceAuthority($advancePayment,$user_id)
    {
        $status = false;
        $user = UserModel::find($user_id);
        if($advancePayment->status==1)
        {
            $employeePosition = EmployeePositionModel::where(["Active"=>2,"EmployeeID"=>$advancePayment->EmployeeID])->first();
            if($employeePosition->ManagerID==$user->EmployeeID)
                $status = true;
        }
        else if($advancePayment->status==2) {
            if($advancePayment->category_id<>""){
                $projetCategories = ProjectCategoriesModel::find($advancePayment->category_id);
                if($user->EmployeeID==$projetCategories->manager_id)
                    $status = true;
            }
            else {
                $project = ProjectsModel::find($advancePayment->project_id);
                if($user->EmployeeID==$project->manager_id)
                    $status = true;
            }

        }
        else if($advancePayment->status==3 || $advancePayment->status==4) {
            //TODO arge userları yapıldı şimdilik sonrasında muhasebe onaylatıcı grup id ile değiştirilecek
            $userGroupCount = UserHasGroupModel::where(["user_id"=>$user_id,"group_id"=>58])->count();
            if($userGroupCount>0)
                $status = true;
        }
        return $status;
    }
}
