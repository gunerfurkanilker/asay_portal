<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Library\Cari;
use App\Model\AsayCariModel;
use App\Model\AsayExpenseLogModel;
use App\Model\EmployeePositionModel;
use App\Model\ExpenseDocumentElementModel;
use App\Model\ExpenseDocumentModel;
use App\Model\ExpenseModel;
use App\Model\AsayProjeModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\UserHasGroupModel;
use App\Model\UserModel;
use App\Model\UserTokensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SoapClient;

class ExpenseController extends ApiController
{
    public function expenseList(Request $request)
    {
        $status = ($request->input("status")===false) ? "" : $request->input("status");
        $user = UserModel::find($request->userId);
        $expenseQ = ExpenseModel::select("Expense.*",DB::raw("SUM(ExpenseDocumentElement.price) AS price"))
            ->leftJoin("ExpenseDocument","ExpenseDocument.expense_id","=","Expense.id")
            ->leftJoin("ExpenseDocumentElement","ExpenseDocumentElement.document_id","=","ExpenseDocument.id")
            ->where(["Expense.active"=>1,"Expense.EmployeeID"=>$user->EmployeeID])
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
        $user = UserModel::find($request->userId);
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
        $AsayExpense->EmployeeID         = $user->EmployeeID;
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
        $expenseDocument->document_date     = date("Y-m-d H:i:s",strtotime($request->document_date));
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

    public function expenseAuthority($asayExpense,$user_id)
    {
        $status = false;
        $user = UserModel::find($user_id);
        if($asayExpense->status==1)
        {
            $employeePosition = EmployeePositionModel::where(["Active"=>2,"EmployeeID"=>$asayExpense->EmployeeID])->first();
            if($employeePosition->ManagerID==$user->EmployeeID)
                $status = true;
        }
        else if($asayExpense->status==2) {
            if($asayExpense->category_id<>""){
                $projetCategories = ProjectCategoriesModel::find($asayExpense->category_id);
                if($user->EmployeeID==$projetCategories->manager_id)
                    $status = true;
            }
            else {
                $project = ProjectsModel::find($asayExpense->project_id);
                if($user->EmployeeID==$project->manager_id)
                    $status = true;
            }

        }
        else if($asayExpense->status==3 || $asayExpense->status==4) {
            //TODO arge userları yapıldı şimdilik sonrasında muhasebe onaylatıcı grup id ile değiştirilecek
            $userGroupCount = UserHasGroupModel::where(["user_id"=>$user_id,"group_id"=>58])->count();
            if($userGroupCount>0)
                $status = true;
        }
        return $status;
    }

    public function getExpense(Request $request)
    {
        $user_id    = $request->userId;
        $user       = UserModel::find($user_id);
        $expenseId = $request->input("expense_id");
        $asayExpense = ExpenseModel::find($expenseId);

        if($asayExpense===null)
        {
            return response([
                'status' => false,
                'message' => "Harcama Belgesi Bulunamadı"
            ], 200);
        }
        else if($asayExpense->EmployeeID==$user->EmployeeID)
        {
            return response([
                'status' => true,
                'data' => $asayExpense
            ], 200);
        }
        else
        {
            $status = self::expenseAuthority($asayExpense,$user_id);
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
                    'data' => $asayExpense
                ], 200);
            }
        }
    }

    public function getExpenseDocument(Request $request)
    {
        $user_id = $request->userId;
        $user = UserModel::find($user_id);
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
            $documentElement = ExpenseDocumentElementModel::where(["document_id"=>$asayExpenseDocument->id,"active"=>1])->get();
            if($asayExpense->EmployeeID==$user->EmployeeID)
            {
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
                $status = self::expenseAuthority($asayExpense,$user_id);
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
                        'data' =>
                            [
                                "document"          =>  $asayExpenseDocument,
                                "documentElement"   => $documentElement
                            ]
                    ], 200);
                }
            }
        }
    }

    public function expenseDocumentList(Request $request)
    {
        $user = UserModel::find($request->userId);
        $expenseId = $request->input("expense_id");
        $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*",DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
            ->where(["ExpenseDocument.active"=>1,"ExpenseDocument.expense_id"=>$expenseId,"Expense.EmployeeID"=>$user->EmployeeID])
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
        $netsisCariKod = $request->input("netsisCariKod")!==null ? $request->input("netsisCariKod") : "";
        if($netsisCariKod=="") {
            $cariTip = $request->input("cariTip")!==null ? $request->input("cariTip") : "";
            $cariler = [];
            if($cariTip==0)
            {
                $currents = DB::connection('sqlsrvn')->select("SELECT CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM FROM TBLCASABIT WHERE CARI_KOD= :cari_kod",["cari_kod"=>$netsisCariKod]);
                foreach ($currents as $current) {
                    $current->netsis = 1;
                    $cariler[] = $current;
                }
            }
            else
            {
                $currents = AsayCariModel::where(["id"=>$netsisCariKod])->get();
                foreach ($currents as $current) {
                    $current->netsis = 0;
                    $cariler[] = $current;
                }
            }
        }
        else{
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
        $status = ($request->input("status")!==null) ? $request->input("status") : "";
        $status = $status=="0" ? "1" : $status;
        $user = UserModel::find($request->userId);
        $employeeManagers = EmployeePositionModel::where(["Active"=>2,"ManagerId"=>$user->EmployeeID])->pluck("EmployeeID");
        $projects   = ProjectsModel::where(["manager_id"=>$user->EmployeeID])->pluck("id");
        $categories = ProjectCategoriesModel::where(["manager_id"=>$user->EmployeeID])->pluck("id");


        $expenseQ = ExpenseModel::select("Expense.*",DB::raw("SUM(ExpenseDocumentElement.price) AS price"))
            ->leftJoin("ExpenseDocument","ExpenseDocument.expense_id","=","Expense.id")
            ->leftJoin("ExpenseDocumentElement","ExpenseDocumentElement.document_id","=","ExpenseDocument.id")
            ->where(["Expense.active"=>1]);

            $expenseQ->where(function($query) use($projects,$categories,$employeeManagers,$status){
                if(count($projects)>0 && ($status==2 || $status==""))
                    $query->whereIn("project_id",$projects);
                if(count($categories)>0 && ($status==2 || $status==""))
                    $query->whereIn("category_id",$categories,"OR");
                if(count($employeeManagers)>0 && ($status==1 || $status==""))
                    $query->whereIn("EmployeeID",$employeeManagers,"OR");
            });
        $expenseQ->groupBy("Expense.id")->orderBy("Expense.created_date","DESC");
        if($status=="")
            $statusArray = [1,2];
        else if($status<>3)
            $statusArray[] = $status;
        $userGroupCount = UserHasGroupModel::where(["user_id"=>$request->userId,"group_id"=>58])->count();
        if($userGroupCount>0 && ($status==3 || $status==""))
            $statusArray[] = 3;
        $expenseQ->whereIn("Expense.status",$statusArray);

        $data["expenses"] = $expenseQ->get();

        return response([
            'status' => true,
            'data' => $data
        ], 200);
    }

    public function expenseDelete(Request $request)
    {
        $expenseId = $request->expenseId;
        if($expenseId===null)
        {
            return response([
                'status' => false,
                'message' => "Masraf Id Boş Olamaz"
            ], 200);
        }
        $user = UserModel::find($request->userId);
        $expenseQ = ExpenseModel::where(["id"=>$expenseId,"EmployeeID"=>$user->EmployeeID,"status"=>0]);
        if($expenseQ->count()==0)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
        $expense = $expenseQ->update(["active"=>0]);

        $documentsQ = ExpenseDocumentModel::where(["expense_id"=>$expenseId]);
        foreach ($documentsQ->get() as $document) {
            ExpenseDocumentElementModel::where(["document_id"=>$document->id])->update(["active"=>0]);
        }
        $documentsQ->update(["active"=>0]);
        return response([
            'status' => true,
            'message' => "Kayıt Silindi"
        ], 200);
    }


    public function deleteDocument(Request $request)
    {
        $documentId = $request->documentId;
        if($documentId===null)
        {
            return response([
                'status' => false,
                'message' => "Belge Id Boş Olamaz"
            ], 200);
        }

        $user = UserModel::find($request->userId);

        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);
        if($expense->EmployeeID!=$user->EmployeeID)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
        ExpenseDocumentElementModel::where(["document_id"=>$documentId])->update(["active"=>0]);
        $document->active=0;
        $documentResult = $document->save();
        if($documentResult){
            return response([
                'status' => true,
                'message' => "Belge Silme Başarılı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Silme Başarısız"
            ], 200);
        }

    }



    public function userTakeBack(Request $request)
    {
        $expenseId = $request->expenseId;
        if($expenseId===null)
        {
            return response([
                'status' => false,
                'message' => "Masraf Id Boş Olamaz"
            ], 200);
        }

        $user = UserModel::find($request->userId);
        $expenseQ = ExpenseModel::where(["id"=>$expenseId,"EmployeeID"=>$user->EmployeeID,"active"=>1])->where("status","<=",1);
        if($expenseQ->count()==0)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
        $expense = $expenseQ->update(["status"=>0]);

        $documentsQ = ExpenseDocumentModel::where(["expense_id"=>$expenseId]);
        $documentsQ->update(["manager_status"=>0,"pm_status"=>0,"accounting_status"=>0]);
        return response([
            'status' => true,
            'message' => "Geri Alındı"
        ], 200);
    }

    public function expenseDocumentConfirm(Request $request)
    {
        $user_id = $request->userId;
        $documentId = $request->documentId;
        if($documentId===null)
        {
            return response([
                'status' => false,
                'message' => "Belge Id Boş Olamaz"
            ], 200);
        }
        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);
        $status = self::expenseAuthority($expense,$user_id);
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
            $document->netsis = 2;
        }

        if($expense->status==1)
        {
            $document->manager_status = $confirm;
            if($confirm==2){
                $document->accounting_status = 2;
                $document->pm_status = 2;
            }
        }
        else if($expense->status==2){
            $document->pm_status = $confirm;
            if($confirm==2){
                $document->accounting_status = 2;
            }
        }
        else if($expense->status==3)
            $document->accounting_status = $confirm;

        $documentResult = $document->save();
        if($documentResult){
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


    public function documentConfirmTakeBack(Request $request)
    {
        $user_id = $request->userId;
        $documentId = $request->documentId;
        if($documentId===null)
        {
            return response([
                'status' => false,
                'message' => "Belge Id Boş Olamaz"
            ], 200);
        }
        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);

        $status = self::expenseAuthority($expense,$user_id);
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if($expense->status==1){
            $document->manager_status       = 0;
            $document->pm_status        = 0;
            $document->accounting_status    = 0;
            $document->netsis=0;
        }
        else if($expense->status==2){
            $document->pm_status        = 0;
            $document->accounting_status    = 0;
            $document->netsis=0;
        }
        else if($expense->status==3){
            $document->accounting_status    = 0;
            $document->netsis               = 0;
        }
        $documentResult = $document->save();

        if($documentResult){
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

    public function expenseComplete(Request $request)
    {
        $user_id = $request->userId;
        $expenseId = $request->expenseId;
        if($expenseId===null)
        {
            return response([
                'status' => false,
                'message' => "Masraf Id Boş Olamaz"
            ], 200);
        }
        $expense = ExpenseModel::find($expenseId);
        $status = self::expenseAuthority($expense,$user_id);
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        if($expense->status==1)
            $column = "manager_status";
        else if($expense->status==2)
            $column = "pm_status";
        $expenseQ = ExpenseModel::where(["id"=>$expenseId,"active"=>1])->whereIn("status",[$expense->status]);
        $expenseDocumentCount = ExpenseDocumentModel::where(["expense_id"=>$expenseId,"active"=>1,$column=>0])->count();

        if($expenseQ->count()==0 || $expenseDocumentCount>0)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }
        if ($column == "manager_status")
            $expenseResult = $expenseQ->update(["status"=>2]);
        if ($column == "pm_status")
            $expenseResult = $expenseQ->update(["status"=>3]);
        //TODO Mail gönderilecek

        if($expenseResult){
            return response([
                'status' => true,
                'message' => "Tamamlandı"
            ], 200);
        }
        else {
            return response([
                'status' => false,
                'message' => "Tamamlama Başarısız"
            ], 200);
        }
    }

    public function SendCurrentToNetsis(Request $request)
    {
        $Cari = new \stdClass();
        $Cari->CariAdres			 = $request->input("CariAdres");
        $Cari->CariEmail			 = "";
        $Cari->CariFax				 = $request->input("CariFax");
        $Cari->CariHesapTipi		 = "K";
        $Cari->CariIl				 = $request->input("CariIl");
        $Cari->CariIlce				 = $request->input("CariIlce");
        $Cari->CariIsim				 = $request->input("CariIsim");
        $Cari->CariKodCRM			 = "";
        $Cari->CariKodNetsis		 = "";
        $Cari->CariMusteriTipi		 = "S";
        $Cari->CariPostaKodu		 = "";
        $Cari->CariTCKN				 = $request->input("CariTCKN");
        $Cari->CariTelefon			 = $request->input("CariTelefon");
        $Cari->CariUlkeKodu			 = $request->input("CariUlkeKodu");
        $Cari->CariVergiDairesi		 = $request->input("CariVergiDairesi");
        $Cari->CariVergiNo			 = $request->input("CariVergiNo");
        $Cari->CariWebAdresi		 = "";
        $Cari->DovizliCari			 = "true";
        $Cari->Isletme 				 = "Asay_Iletisim";


        $set = array();
        $set["CariIsim"] 			= $request->input("CariIsim");
        $set["CariUlkeKodu"] 		= $request->input("CariUlkeKodu");
        $set["CariIl"] 				= $request->input("CariIl");
        $set["CariIlce"] 			= $request->input("CariIlce");
        $set["CariAdres"] 			= $request->input("CariAdres");
        $set["CariVergiDairesi"] 	= $request->input("CariVergiDairesi");
        $set["CariVergiNo"] 		= $request->input("CariVergiNo");
        $set["CariTelefon"] 		= $request->input("CariTelefon");
        $set["CariFax"] 			= $request->input("CariFax");
        if($request->input("NetsisKod")!="0")
        {
            $set["NetsisCariKod"] 	= $request->input("NetsisKod");
            $set["Netsis"] 			= 1;
        }
        else
        {
            $set["Netsis"] 		= 0;
        }
        AsayCariModel::where(["ID"=>$request->input("CariId")])->update($set);

        if($set["Netsis"]==0)
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
            try
            {
                $soap = new SoapClient($wsdl, $options);
                $data = $soap->CariEkle(array("_cari"=>$Cari));
            }
            catch(Exception $e)
            {
                die($e->getMessage());
            }

            $sonuc["CariKod"]	= $data->CariEkleResult->Aciklama;
            $sonuc["Sonuc"]		= $data->CariEkleResult->Sonuc;



            if($sonuc["Sonuc"]==false):
                return response([
                    'status' => false,
                    'message' => $data->CariEkleResult->Aciklama
                ], 200);
            else:
                $set = array();
                $set["Netsis"] 			= 1;
                $set["NetsisCariKod"] 	= $sonuc["CariKod"];
                AsayCariModel::where(["ID"=>$request->input("CariId")])->update($set);

                $set2["netsis_carikod"] = $sonuc["CariKod"];
                $set2["cari_tip"] 	    = "0";
                ExpenseDocumentModel::where(["id"=>$request->input("DocumentId")])->update($set2);
                return response([
                    'status' => true,
                    'data' => $sonuc
                ], 200);
            endif;
        }
        else
        {
            $sonuc["netsis_carikod"]	= $request->input("NetsisKod");
            $sonuc["Sonuc"]		        = true;
            $set2 = array();
            $set2["netsis_carikod"] 	= $request->input("NetsisKod");
            $set2["cari_tip"] 	    = "0";
            ExpenseDocumentModel::where(["id"=>$request->input("DocumentId")])->update($set2);

            return response([
                'status' => true,
                'data' => $sonuc
            ], 200);
        }
    }

    public function SendExpenseToNetsis(Request $request)
    {
        $user_id = $request->userId;
        $expenseId = $request->input("expenseId");

        //MASRAF DETAYLARI
        $expense = ExpenseModel::find($expenseId);
        $status = self::expenseAuthority($expense,$user_id);
        if($status==false)
        {
            return response([
                'status' => false,
                'message' => "Yetkisiz İşlem"
            ], 200);
        }

        //Personel bilgileri kontrol ediliyor.
        $user = UserModel::find($request->userId);

        //Çalışan İşletme Belirlenmesi
        if($user->user_property->company=="Elektronik"){
            $company = "Asay_Elektronik";
        }
        elseif($user->user_property->company=="Enerji"){
            $company = "Asay_Enerji";
        }
        elseif($user->user_property->company=="iletisim"){
            $company = "Asay_Iletisim";
        }
        elseif($user->user_property->company=="VAD"){
            $company = "Asay_Vad_Otomasyon";
        }


        if($expense->expense_type=="İş Avansı")
        {
            $Query = "CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%'";
        }
        elseif($expense->expense_type=="Seyahat Avansı")
        {
            $Query = "CARI_KOD LIKE 'PS%'";
        }

        //CARİ KOD ÖĞREN
        $PersonelCariKodu = "";
        $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE ".$Query." and EMAIL= :email",["email"=>$user->email]);
        if(count($CariKod)>0)
            $PersonelCariKodu= $CariKod[0]->CARI_KOD;


        if($PersonelCariKodu=="")
        {
            $setLog["EXPENSE_ID"]   = $expenseId;
            $setLog["LOG"]		    = "Personel Cari Kodu Netsisde Bulunamadı. Mail adresini kontrol ediniz.<br>Mail Adresi:".$user->email;
            AsayExpenseLogModel::insert($setLog);
            return response([
                'status' => false,
                'message' => $setLog["LOG"]
            ], 200);
        }

        //MASRAF BELGELERİ
        $documents = ExpenseDocumentModel::where(["active"=>1,"netsis"=>0,"accounting_status"=>1,"expense_id"=>$expenseId])->get();
        if($documents->count()==0){
            return response([
                'status' => false,
                'data' => "Aktarılacak Belge Bulunamadı"
            ], 200);
        }
        //MASRAF BELGE KALEMLERİ
        $document_element = new \stdClass();
        foreach($documents as $key => $value)
        {
            $document_elements[$value->id] = ExpenseDocumentElementModel::where(["active"=>1,"document_id"=>$value->id])->get();
        }
        $DurumHataSay = 0;

        //Proje kodu ve plasiyer kodu belirleniyor

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
            $soap = new SoapClient($wsdl, $options);
        }
        catch(Exception $e)
        {
            return response([
                'status' => false,
                'data' => $e->getMessage()
            ], 200);
        }
        $project = ProjectsModel::find($expense->project_id);

        foreach($documents as $key => $value)
        {
            $MasrafFormu = new \stdClass();
            $MasrafFormu->ProjeKodu 	= $project->project_code;;
            $MasrafFormu->PlasiyerKodu 	= $project->plasiyer_code;;
            $Silindi = 0;
            if($value->document_type=="Fişli")
            {
                $MasrafKalem = array();
                $MasrafFormu->Fisli 	= "E";
                $MasrafFormu->Belgeli 	= "H";

                $kdvoran 		= 0;
                $TutarToplam 	= 0;
                $MatrahToplam	= 0;
                foreach($document_elements[$value->id] as $key2 => $value2)
                {
                    $KdvOran 	= $value2->kdv;
                    $Matrah 	= $value2->price*$value2->quantity;
                    $Adet 		= $value2->quantity;
                    $Tutar 		= $value2->amount;

                    $MatrahToplam 	+= $Matrah;
                    $TutarToplam 	+= $Tutar;

                    //kdv dahil ise matrah=tutar
                    $MasrafKalem[0]["Aciklama"] 	= $value2->content;
                    $MasrafKalem[0]["FisNo"]		= $value->document_number;
                    $MasrafKalem[0]["GiderKodu"]	= $value2->expense_account;
                    $MasrafKalem[0]["KdvMatrahi"]	= $MatrahToplam;
                    $MasrafKalem[0]["KdvOran"]		= $KdvOran;
                    $MasrafKalem[0]["Miktar"]		= $Adet;
                    $MasrafKalem[0]["Tutar"]		= $TutarToplam;
                    /*if($value2["MSTATUS"]==2)
                        $Silindi++;*/
                }
                $CariAciklama = $MasrafKalem[0]["Aciklama"];
            }
            elseif($value->document_type=="Faturalı")
            {
                $MasrafKalem = array();
                $MasrafFormu->Fisli 	= "H";
                $MasrafFormu->Belgeli 	= "E";

                foreach($document_element[$value->id] as $key2 => $value2)
                {
                    //kdv dahil ise matrah=tutar
                    $MasrafKalem[$key2]["Aciklama"] 	= $value2->content;
                    $MasrafKalem[$key2]["FisNo"]		= $value->document_number;
                    $MasrafKalem[$key2]["GiderKodu"]	= $value2->expense_account;
                    $MasrafKalem[$key2]["KdvMatrahi"]	= $value2->price*$value2->quantity;
                    $MasrafKalem[$key2]["KdvOran"]		= $value2->kdv;
                    $MasrafKalem[$key2]["Miktar"]		= $value2->quantity;
                    $MasrafKalem[$key2]["Tutar"]		= $value2->amount;
                    /*if($value2["MSTATUS"]==2)
                        $Silindi++;
                    */
                }
                $CariAciklama = $value->document_number." NOLU ".$expense->description;
            }
            //if($Silindi>0 && $Silindi==count($MasrafKalem)) continue;

            $MasrafFormu->Aciklama 	= $CariAciklama;
            $MasrafFormu->BelgeNo 	= $value->document_number;
            $MasrafFormu->CariKod 	= $value->netsis_carikod;
            $MasrafFormu->Kalemler 	= $MasrafKalem;

            $MasrafFormu->Tarih 	= $value->document_date;
            $MasrafFormu->PersonelCariKodu 	= $PersonelCariKodu;
            $MasrafFormu->KdvDahil 	= "E";

            $dbA = date("Y",strtotime($value->document_date));
            try
            {
                $data = $soap->MasrafFormuKaydet(array("_MasrafForm"=>$MasrafFormu,"_IsletmeKodu"=>$company,"SirketAdi"=>"ASAYGROUP".$dbA));
            }
            catch(Exception $e)
            {
                return response([
                    'status' => false,
                    'data' => $e->getMessage()
                ], 200);
            }

            if($data->MasrafFormuKaydetResult->Sonuc==1)
            {
                $set["netsis"] 			= 1;
                $set["netsis_document_number"] 	= "'".str_replace(" numaralı masraf dekontu kaydedildi","",$data->MasrafFormuKaydetResult->Aciklama)."'";
                ExpenseDocumentModel::where(["id"=>$value->id])->update($set);

                $aktar[$value->id]["sonuc"]     = true;
                $aktar[$value->id]["aciklama"]  = $data->MasrafFormuKaydetResult->Aciklama;
                $aktar[$value->id]["belgeno"]   = $value->document_number;
            }
            else
            {
                $aktar[$value->id]["sonuc"]     = false;
                $aktar[$value->id]["aciklama"]  = $data->MasrafFormuKaydetResult->Aciklama;
                $aktar[$value->id]["belgeno"]   = $value->document_number;
                $DurumHataSay++;
            }
            $setLog["EXPENSE_ID"] = $expenseId;
            $setLog["DOCUMENT_ID"] = $value->id;
            $setLog["BELGE_NO"] = $value->document_number;
            $setLog["LOG"]		= trim($data->MasrafFormuKaydetResult->Aciklama);
            AsayExpenseLogModel::insert($setLog);
        }
        if($DurumHataSay==0)
        {
            ExpenseModel::where(["id"=>$expenseId])->update(["status"=>3]);
            return response([
                'status' => true,
                'data' => "Masraf Belgeleri Netsise Aktarıldı"
            ], 200);
        }
        else
        {
            return response([
                'status' => false,
                'data' => "Belgelerin Tamamı Netsise Aktarılamadı"
            ], 200);
        }
    }
}
