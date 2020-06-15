<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Model\AsayCariModel;
use App\Model\ExpenseDocumentElementModel;
use App\Model\ExpenseDocumentModel;
use App\Model\ExpenseModel;
use App\Model\AsayProjeModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserModel;
use App\Model\UserTokensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends ApiController
{
    public function expenseList(Request $request)
    {
        $status = ($request->input("status")===false) ? "" : $request->input("status");
        $user = UserModel::find($request->userId);
        $expenseQ = ExpenseModel::select("Expense.*",DB::raw("SUM(ExpenseDocumentElement.price) AS price"))
            ->leftJoin("ExpenseDocument","ExpenseDocument.expense_id","=","Expense.id")
            ->leftJoin("ExpenseDocumentElement","ExpenseDocumentElement.document_id","=","ExpenseDocument.id")
            ->where(["Expense.active"=>1,"Expense.user_id"=>$user->id])
            ->groupBy("Expense.id")->orderBy("Expense.created_date","DESC");
        if($status<>0)
            $expenseQ->where(["Expense.status"=>$status]);

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
        $user = UserModel::find($request->userId);

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
        $post["name"]                = $request->input("name");
        $post["expense_type"]        = $request->input("expense_type");
        $post["project_id"]          = $request->input("project_id");
        $post["category_id"]         = $request->input("category_id")!==null ? $request->input("category_id") : "";
        $post["description"]         = $request->input("description");
        $post["expense_id"]          = $request->input("expense_id");
        $expense_id                  = $post["expense_id"];
        if($post["type"]=="onay")
        {
            $expenseDocument = ExpenseDocumentModel::where(["expense_id"=>$expense_id,"active"=>1]);
            if($expenseDocument->count()==0)
            {
                return response([
                    'status' => false,
                    'message' => "Masrafa Belge Eklemeden Onaya Gönderilemez."
                ], 200);
            }
            $documentCari = ExpenseDocumentModel::where(["expense_id"=>$expense_id,"active"=>1,"document_type"=>"Faturalı"])
                ->where(function ($query) {
                    $query->where('netsis_carikod', '=', "0")
                        ->orWhere('netsis_carikod', '=', "")
                        ->orWhere('netsis_carikod', '=', null);
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
            $AsayExpense = new ExpenseModel();
        else
            $AsayExpense = ExpenseModel::find($expense_id);

        $AsayExpense->name               = $post["name"];
        $AsayExpense->project_id         = $post["project_id"]!==null ? $post["project_id"] : "";
        $AsayExpense->category_id        = $post["category_id"]!==null ? $post["category_id"] : "";
        $AsayExpense->expense_type       = $post["expense_type"];
        $AsayExpense->description        = $post["description"];
        $AsayExpense->user_id            = $user->id;
        if($post["type"]=="kaydet")
        {
            $AsayExpense->status = 0;
        }
        elseif($post["type"]=="onay")
        {
            $AsayExpense->status = 1;
        }

        if($AsayExpense->save())
        {
            if($post["type"]=="onay")
            {
                ExpenseDocumentModel::update(["status"=>1])->where(["expense_id"=>$expense_id]);
            }
            return response([
                'status' => true,
                'type' => $post["type"],
                'data' => [
                    "expense_id"=>$AsayExpense->id
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

    public function documentSave(Request $request)
    {
        $documentId = $request->document_id!==null ? $request->document_id : "";
        if($documentId!="")
        {
            $expenseDocument    = ExpenseDocumentModel::find($documentId);
            if($expenseDocument===null)
                $expenseDocument = new ExpenseDocumentModel();
        }
        else
        {
            $expenseDocument = new ExpenseDocumentModel();
        }
        $expenseDocument->expense_id        = $request->expense_id;
        $expenseDocument->cari_tip          = $request->cari_tip;
        $expenseDocument->document_date     = date("Y-m-d",strtotime($request->document_date));
        $expenseDocument->document_number   = $request->document_number;
        $expenseDocument->document_type     = $request->document_type;
        $expenseDocument->currency          = $request->currency;
        $expenseDocument->netsis_carikod    = $request->netsis_carikod;
        $expenseDocument->active            = 1;
        $expenseDocument->save();
        $documentId = $expenseDocument->id;

        ExpenseDocumentElementModel::where(["document_id"=>$documentId])->update(["active"=>0]);
        if(count($request->element))
        {
            foreach ($request->element as $element) {
                $elementId = isset($element["element_id"]) ? $element["element_id"] : "";
                if($elementId!="")
                {
                    $expenseDocumentElement    = ExpenseDocumentElementModel::find($elementId);
                    if($expenseDocumentElement===null)
                        $expenseDocumentElement = new ExpenseDocumentElementModel();
                }
                else
                {
                    $expenseDocumentElement = new ExpenseDocumentElementModel();
                }
                $expenseDocumentElement->document_id        = $documentId;
                $expenseDocumentElement->expense_account    = $element["expense_account"];
                $expenseDocumentElement->content            = $element["content"];
                $expenseDocumentElement->quantity           = $element["quantity"];
                $expenseDocumentElement->kdv                = $element["kdv"];
                $expenseDocumentElement->price              = $element["price"];
                $expenseDocumentElement->amount             = number_format(($element["quantity"]*$element["price"])*(($element["kdv"]/100)+1), 2, '.', '');
                $expenseDocumentElement->active             = 1;
                $expenseDocumentElement->save();
            }
        }

        return response([
            'status' => true,
            'message' => "Belge Kaydı Yapıldı",
        ], 200);

    }

    public function getExpense(Request $request)
    {
        $user_id = $request->userId;
        $expenseId = $request->input("expense_id");
        $asayExpense = ExpenseModel::find($expenseId);

        if($asayExpense===null)
        {
            return response([
                'status' => false,
                'message' => "Harcama Belgesi Bulunamadı"
            ], 200);
        }
        else if($asayExpense->user_id==$user_id)
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

    public function getExpenseDocument(Request $request)
    {
        $user_id = $request->userId;
        $documentId = $request->input("document_id");
        $asayExpenseDocument = ExpenseDocumentModel::find($documentId);

        if($asayExpenseDocument===null)
        {
            return response([
                'status' => false,
                'message' => "Belge Bulunamadı"
            ], 200);
        }
        else
        {
            $asayExpense = ExpenseModel::find($asayExpenseDocument->expense_id);
            if($asayExpense->user_id==$user_id)
            {
                $documentElement = ExpenseDocumentElementModel::where(["document_id"=>$asayExpenseDocument->id])->get();

                return response([
                    'status' => true,
                    'data' =>
                    [
                        "document"          =>  $asayExpenseDocument,
                        "documentElement"   => $documentElement
                    ]
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
    }

    public function expenseDocumentList(Request $request)
    {
        $expenseId = $request->input("expense_id");
        $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*",DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
            ->where(["ExpenseDocument.active"=>1,"ExpenseDocument.expense_id"=>$expenseId,"Expense.user_id"=>$request->userId])
            ->leftJoin("ExpenseDocumentElement","ExpenseDocumentElement.document_id","=","ExpenseDocument.id")
            ->leftJoin("Expense","ExpenseDocument.expense_id","=","Expense.id")
            ->groupBy("ExpenseDocument.id");

        $SumTutar = 0;
        if($expenseDocumentsQ->count()>0)
        {
            $expenseDocuments = $expenseDocumentsQ->get();
            foreach ($expenseDocuments as $expenseDocument) {
                if($expenseDocument->cari_tip==1)
                {
                    $Cari = AsayCariModel::find($expenseDocument->netsis_carikod);
                    $expenseDocument->CARI_ISIM = $Cari->CariIsim;
                }
                elseif($expenseDocument->document_type=="Faturalı" && $expenseDocument->cari_tip<>1)
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
        $parabirimleri = array(
            'ISIM' => 'TRY',
            'SIRA' => '00'
        );

        $ParaQ = DB::connection('sqlsrv_net')->select("SELECT SIRA,dbo.TO_ENG(ISIM) as ISIM FROM KUR");
        array_unshift($ParaQ,$parabirimleri);

        return response([
            'status' => true,
            'data' => $ParaQ
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

    public function currentSave(Request $request)
    {
        $currentId = $request->CariID!==null ? $request->CariID : "";
        if($currentId!="")
        {
            $expenseCurrentCount      = AsayCariModel::where(["id"=>$currentId])->count();
            if($expenseCurrentCount>0)
                $expenseCurrent = AsayCariModel::find($currentId);
            else
                $expenseCurrent = new AsayCariModel();
        }
        else
        {
            $expenseCurrent = new AsayCariModel();
        }
        $expenseCurrent->CariIsim		    = $request->CariIsim;
        $expenseCurrent->CariUlkeKodu	    = $request->CariUlkeKodu;
        $expenseCurrent->CariIl			    = $request->CariIl;
        $expenseCurrent->CariIlce		    = $request->CariIlce;
        $expenseCurrent->CariAdres		    = $request->CariAdres;
        $expenseCurrent->CariVergiDairesi   = $request->CariVergiDairesi;
        $expenseCurrent->CariVergiNo		= $request->CariVergiNo;
        $expenseCurrent->CariTelefon		= $request->CariTelefon;
        $expenseCurrent->CariFax			= $request->CariFax;
        $expenseCurrent->Netsis			    = 0;
        if($expenseCurrent->save())
        {
            return response([
                'status' => true,
                'data' => ["cariId"=>$expenseCurrent->ID]
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

    public function listNetsisCurrent(Request $request)
    {
        $search = $request->input("search");
        $cariler = [];

        $currents = DB::connection('sqlsrvn')->select("SELECT TOP 10 CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM FROM TBLCASABIT WHERE (CARI_KOD LIKE 'D%' OR (CARI_KOD LIKE 'S%' AND CARI_KOD NOT LIKE 'S%D' AND CARI_KOD NOT LIKE 'S%E')) AND CARI_ISIM LIKE N'%".$search."%'");
        foreach ($currents as $current) {
            $current->netsis = 1;
            $cariler[] = $current;
        }

        $currents = AsayCariModel::where(["Netsis"=>0])->where('CariIsim', 'like', '%' . $search . '%')->get();
        foreach ($currents as $current) {
            $current->netsis = 0;
            $cariler[] = $current;
        }
        $array = [];
        foreach($cariler as $key => $cari) {
            if ($cari->netsis == 0) {
                $array[$key]["id"] = $cari->ID;
                $array[$key]["value"] = $cari->CariIsim;
                $array[$key]["netsis"] = 1;
            } else {
                $array[$key]["id"] = $cari->CARI_KOD;
                $array[$key]["value"] = $cari->CARI_ISIM;
                $array[$key]["netsis"] = 0;
            }
        }
        return response([
            'status' => true,
            'data' => $array
        ], 200);
    }


    public function expensePendingList(Request $request)
    {
        //1:Proje Yönetici, 2:Muhasebe
        $status = ($request->input("status")!==null) ? $request->input("status") : 1;
        $status = $status==0 ? 1 : $status;
        $user = UserModel::find($request->userId);
        $projects   = ProjectsModel::where(["manager_id"=>$user->id])->pluck("id");
        $categories = ProjectCategoriesModel::where(["manager_id"=>$user->id])->pluck("id");
        if(count($projects)==0 && count($categories)==0)
        {
            return response([
                'status' => false,
                'message' => "Yöneticisi Olduğunuz Proje Yok.",
            ], 200);
        }

        $expenseQ = ExpenseModel::select("Expense.*",DB::raw("SUM(ExpenseDocumentElement.price) AS price"))
            ->leftJoin("ExpenseDocument","ExpenseDocument.expense_id","=","Expense.id")
            ->leftJoin("ExpenseDocumentElement","ExpenseDocumentElement.document_id","=","ExpenseDocument.id")
            ->where(["Expense.active"=>1,"Expense.user_id"=>$user->id]);
        if($status==1)
        {
            $expenseQ->where(function($query) use($projects,$categories){
                $query->whereIn("project_id",$projects);
                $query->whereIn("category_id",$categories,"OR");
            });
        }
        $expenseQ->groupBy("Expense.id")->orderBy("Expense.created_date","DESC");
            $expenseQ->where(["Expense.status"=>$status]);

        $data["expenses"] = $expenseQ->get();

        return response([
            'status' => true,
            'data' => $data
        ], 200);
    }

}
