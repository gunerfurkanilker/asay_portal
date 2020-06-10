<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Model\AsayCariModel;
use App\Model\AsayExpenseDocumentElementModel;
use App\Model\AsayExpenseDocumentModel;
use App\Model\AsayProjeModel;
use App\Model\UserModel;
use App\Model\AsayExpenseModel;
use App\Model\UserTokensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends ApiController
{
   public function expenseList(Request $request)
   {
       $status = ($request->input("status")===false) ? "" : $request->input("status");
       $user = UserModel::find(UserTokensModel::where(["user_token"=>$request->input("token")])->first()->user_id);
       $expenseQ = AsayExpenseModel::select("asay_expense.*",DB::raw("SUM(asay_expense_document_element.TUTAR) AS TUTAR"))
           ->leftJoin("asay_expense_document","asay_expense_document.EXPENSE_ID","=","asay_expense.ID")
           ->leftJoin("asay_expense_document_element","asay_expense_document_element.DOCUMENT_ID","=","asay_expense_document.ID")
           ->where(["asay_expense.ACTIVE"=>1,"asay_expense.USER_ID"=>$user->id])
           ->groupBy("asay_expense.ID")->orderBy("asay_expense.DATE_CREATE","DESC");
       if($status<>0)
           $expenseQ->where(["asay_expense.STATUS"=>$status]);

       $data["manager"] = $user->user_property->manager;
       $data["expenses"] = $expenseQ->get();

       for($i = 0; $i < 2; $i++)
       {
           $Query = "";
           if($i==0)
           {
               $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%' and EMAIL= :email",["email"=>$user->email]);
               if(count($CariKod)>0)
                   $PersonelCariKodu["Is"] = $CariKod[0]->CARI_KOD;
           }
           elseif($i==1)
           {
               $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'PS%' and EMAIL= :email",["email"=>$user->email]);
               if(count($CariKod)>0)
                   $PersonelCariKodu["Seyahat"] = $CariKod[0]->CARI_KOD;
               else
                   $PersonelCariKodu["Seyahat"] = "0";
           }
       }
       foreach($PersonelCariKodu as $key => $value)
       {
           $data["Tutar"][$key] = 0;
           $AvansOzet = DB::connection('sqlsrvn')->select("SELECT * FROM ArnVw_PersonelAvansOzet WHERE CARI_KOD= :carikod", ["carikod"=>$value]);
           if(count($AvansOzet)>0)
           {
               if($AvansOzet[0]->BAKIYE==".00000000")
                   $AvansOzet[0]->BAKIYE = "0";
               $data["Tutar"][$key] = $AvansOzet[0]->BAKIYE;
           }
       }
       return response([
           'status' => true,
           'data' => $data
       ], 200);
   }

   public function getAccountBalance(Request $request)
   {
       $user = UserModel::find(UserTokensModel::where(["user_token"=>$request->input("token")])->first()->user_id);

       $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%' and EMAIL= :email",["email"=>$user->email]);
       if(count($CariKod)>0)
           $PersonelCariKodu["Is"] = $CariKod[0]->CARI_KOD;
       else
           $PersonelCariKodu["Is"] = "0";


       $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'PS%' and EMAIL= :email",["email"=>$user->email]);
       if(count($CariKod)>0)
           $PersonelCariKodu["Seyahat"] = $CariKod[0]->CARI_KOD;
       else
           $PersonelCariKodu["Seyahat"] = "0";


       foreach($PersonelCariKodu as $key => $value)
       {
           $data[$key] = 0;
           $AvansOzet = DB::connection('sqlsrvn')->select("SELECT * FROM ArnVw_PersonelAvansOzet WHERE CARI_KOD= :carikod", ["carikod"=>$value]);
           if(count($AvansOzet)>0)
           {
               if($AvansOzet[0]->BAKIYE==".00000000")
                   $AvansOzet[0]->BAKIYE = "0";
               $data[$key] = $AvansOzet[0]->BAKIYE;
           }
       }

       return response([
           'status' => true,
           'data' => $data
       ], 200);
   }

   public function getCrmProjectCode(Request $request)
   {
       $ProjectCode = $request->input("ProjeKodu");
       $client = new \GuzzleHttp\Client();
       $p = json_decode($client->get($this->crm_url."projeler/projeno/".$ProjectCode)->getBody());
       $replace = array('<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">','</string>');
       $projeler = json_decode(str_replace($replace,"",$p),true);
       if(!$projeler)
       {
           $CrmExternalProjectQ = AsayProjeModel::where(["PROJE_KODU"=>$ProjectCode]);
           if($CrmExternalProjectQ->count()>0)
           {
               $CrmExternalProject = $CrmExternalProjectQ->first();
               $projeler = array();
               $projeler[0]["ownerusercode"]	= $CrmExternalProject->PLASIYER;
               $projeler[0]["projectno"]		= $CrmExternalProject->PROJE_KODU;
               $projeler[0]["salesorderidname"] = $CrmExternalProject->NAME;
           }
       }
       return response([
           'status' => true,
           'data' => $projeler
       ], 200);
   }


   public function expenseSave(Request $request)
   {
       $user = UserModel::find(UserTokensModel::where(["user_token"=>$request->input("token")])->first()->user_id);
       $post["type"]                = $request->input("type");
       $post["NAME"]                = $request->input("NAME");
       $post["EXPENSE_TYPE"]        = $request->input("EXPENSE_TYPE");
       $post["EXPENSE_TYPE_VALUE"]  = $request->input("EXPENSE_TYPE_VALUE");
       $post["MASRAF_SEKLI"]        = $request->input("MASRAF_SEKLI");
       $post["CONTENT"]             = $request->input("CONTENT");
       $post["expense_id"]          = $request->input("expense_id");
       $expense_id                  = $post["expense_id"];
       if($post["type"]=="onay")
       {
           $expenseDocument = AsayExpenseDocumentModel::where(["EXPENSE_ID"=>$expense_id,"ACTIVE"=>1]);
           if($expenseDocument->count()==0)
           {
               return response([
                   'status' => false,
                   'message' => "Masrafa Belge Eklemeden Onaya Gönderilemez."
               ], 200);
           }
           $documentCari = AsayExpenseDocumentModel::where(["EXPENSE_ID"=>$expense_id,"ACTIVE"=>1,"BELGE_TYPE"=>"Faturalı"])
               ->where(function ($query) {
                   $query->where('CARIKOD', '=', "0")
                       ->orWhere('CARIKOD', '=', "")
                       ->orWhere('CARIKOD', '=', null);
               });
           if($documentCari->count()>0)
           {
               return response([
                   'status' => false,
                   'message' => "Onaya Göndermek İçin Carisi Olmayan Belgeleri Düzenleyiniz."
               ], 200);
           }
       }


       if($expense_id=="")
           $AsayExpense = new AsayExpenseModel();
       else
           $AsayExpense = AsayExpenseModel::find($expense_id);
       $AsayExpense->NAME               = $post["NAME"];
       $AsayExpense->EXPENSE_TYPE_VALUE = (isset($post["EXPENSE_TYPE_VALUE"])) ? $post["EXPENSE_TYPE_VALUE"] : "";
       $AsayExpense->EXPENSE_TYPE       = $post["EXPENSE_TYPE"];
       $AsayExpense->MASRAF_SEKLI       = $post["MASRAF_SEKLI"];
       $AsayExpense->CONTENT            = $post["CONTENT"];
       $AsayExpense->USER_ID            = $user->id;
       if($post["type"]=="kaydet")
       {
           $AsayExpense->STATUS = 0;
       }
       elseif($post["type"]=="onay")
       {
           $AsayExpense->STATUS = 1;
       }

       if($AsayExpense->save())
       {
           if($post["type"]=="onay")
           {
               AsayExpenseDocumentModel::update(["STATUS"=>1])->where(["EXPENSE_ID"=>$expense_id]);
           }
           return response([
               'status' => true,
               'type' => $post["type"],
               'data' => [
                   "expense_id"=>$AsayExpense->ID
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

   public function getExpense(Request $request)
   {

        $user_id = UserTokensModel::where(["user_token"=>$request->input("token")])->first()->user_id;
        $expenseId = $request->input("expense_id");
        $asayExpense = AsayExpenseModel::find($expenseId);

        if($asayExpense->USER_ID==$user_id)
        {
            return response([
                'status' => true,
                'data' => $asayExpense
            ], 200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
   }

   public function getExpenseDocuments(Request $request)
   {
       $expenseId = $request->input("expense_id");
       $expenseDocumentsQ = AsayExpenseDocumentModel::select("asay_expense_document.*",DB::raw("SUM(asay_expense_document_element.TUTAR) TTUTAR"))
           ->where(["asay_expense_document.ACTIVE"=>1,"asay_expense_document.EXPENSE_ID"=>$expenseId,"asay_expense.USER_ID"=>$request->userId])
           ->leftJoin("asay_expense_document_element","asay_expense_document_element.DOCUMENT_ID","=","asay_expense_document.ID")
           ->leftJoin("asay_expense","asay_expense_document.EXPENSE_ID","=","asay_expense.ID")
           ->groupBy("asay_expense_document.ID");

       $SumTutar = 0;
       if($expenseDocumentsQ->count()>0)
       {
           $expenseDocuments = $expenseDocumentsQ->get();
           foreach ($expenseDocuments as $expenseDocument) {
               if($expenseDocument->CARI_TIP==1)
               {
                   $Cari = AsayCariModel::find($expenseDocument->CARIKOD);
                   $expenseDocument->CARI_ISIM = $Cari->CariIsim;
               }
               elseif($expenseDocument->BELGE_TYPE=="Faturalı" && $expenseDocument->CARI_TIP<>1)
               {
                   $CariIsim = DB::connection('sqlsrvn')->select("SELECT CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM FROM TBLCASABIT WHERE CARI_KOD= :cari_kod",["cari_kod"=>$expenseDocument->CARIKOD]);
                   if(count($CariIsim)>0)
                   {
                       $expenseDocument->CARI_ISIM = $CariIsim[0]->CARI_ISIM;
                   }
                   else
                   {
                       $expenseDocument->CARI_ISIM = "";
                   }
               }
               else
               {
                   $expenseDocument->CARI_ISIM = "";
               }
               $SumTutar += $expenseDocument->TTUTAR;
           }
       }
       else
       {
           $expenseDocuments = [];
       }

       return response([
           'status' => true,
           'TotalAmount' => $SumTutar,
           'data' => $expenseDocuments,
       ], 200);
   }

   public function getParaBirimleri()
   {
       $parabirimleri["00"] = "TRY";
       $ParaQ = DB::connection('sqlsrv_net')->select("SELECT SIRA,dbo.TO_ENG(ISIM) as ISIM FROM KUR");
       foreach ($ParaQ as $item) {
           $parabirimleri[$item->SIRA] = $item->ISIM;
       }

       return response([
           'status' => true,
           'data' => $parabirimleri
       ], 200);
   }

   public function getMuhasebeGiderHesaplari()
   {
       $GiderHesaplariQ = DB::connection('sqlsrvn')->select("SELECT HESAP_KODU,dbo.TRK2(HS_ADI) AS HS_ADI FROM OFC_GIDER_HESAPLARI");
       foreach ($GiderHesaplariQ as $item) {
           $masraflar[] = $item;
       }

       return response([
           'status' => true,
           'data' => $masraflar
       ], 200);
   }

   public function cariEkle()
   {

   }

}
