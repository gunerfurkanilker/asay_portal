<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use App\Model\AsayExpenseModel;
use App\Model\UserTokensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends ApiController
{
   public function expenseList(Request $request)
   {
       $status = $request->input("status");
       $user = UserModel::find(UserTokensModel::where(["user_token"=>$request->input("token")])->first()->user_id);
       $expenseQ = AsayExpenseModel::select("asay_expense.*",DB::raw("SUM(asay_expense_document_element.TUTAR) AS TUTAR"))
           ->leftJoin("asay_expense_document","asay_expense_document.EXPENSE_ID","=","asay_expense.ID")
           ->leftJoin("asay_expense_document_element","asay_expense_document_element.DOCUMENT_ID","=","asay_expense_document.ID")
           ->where(["asay_expense.ACTIVE"=>1,"asay_expense.USER_ID"=>$user->id])
           ->groupBy("asay_expense.ID")->orderBy("asay_expense.DATE_CREATE","DESC");
       if($status<>0)
           $expenseQ->where(["asay_expense.STATUS"=>$status]);

       $data["expenses"] = $expenseQ->get();

       $data["manager"] = explode(",",explode("CN=",$user->user_property->manager)[1])[0];

       for($i = 0; $i < 2; $i++)
       {
           $Query = "";
           if($i==0)
           {
               $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%' and EMAIL= :email",["email"=>$user->email])[0];
               if(count($data)>0)
                   $data["PersonelCariKodu"]["Is"] = $CariKod->CARI_KOD;
           }
           elseif($i==1)
           {
               $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'PS%' and EMAIL= :email",["email"=>$user->email])[0];
               if(count($data)>0)
                   $data["PersonelCariKodu"]["Seyahat"] = $CariKod->CARI_KOD;
           }
       }
       foreach($data["PersonelCariKodu"] as $key => $value)
       {
           $data["Tutar"][$key] = 0;
           $AvansOzet = DB::connection('sqlsrvn')->select("SELECT * FROM ArnVw_PersonelAvansOzet WHERE CARI_KOD= :carikod", ["carikod"=>$value])[0];
           if($AvansOzet->BAKIYE==".00000000")
               $AvansOzet->BAKIYE = 0;
           $data["Tutar"][$key] = $AvansOzet->BAKIYE;
       }
       return response([
           'status' => true,
           'data' => $data
       ], 200);
   }
}
