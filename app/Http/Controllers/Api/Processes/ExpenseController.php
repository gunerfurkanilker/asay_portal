<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Common\DocumentTypeController;
use App\Http\Controllers\Controller;
use App\Library\Asay;
use App\Library\Cari;
use App\Model\AsayCariModel;
use App\Model\AsayExpenseLogModel;
use App\Model\CompanyModel;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\ExpenseAccountCodesModel;
use App\Model\ExpenseDocumentElementModel;
use App\Model\ExpenseDocumentModel;
use App\Model\ExpenseDocumentTypesModel;
use App\Model\ExpenseModel;
use App\Model\AsayProjeModel;
use App\Model\ExpenseTypesModel;
use App\Model\LogsModel;
use App\Model\NotificationsModel;
use App\Model\ObjectFileModel;
use App\Model\ProcessesSettingsModel;
use App\Model\ProjectCategoriesModel;
use App\Model\ProjectsModel;
use App\Model\TaxOfficesModel;
use App\Model\UserHasGroupModel;
use App\Model\UserTokensModel;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SoapClient;

use Illuminate\Support\Facades\Validator;

class ExpenseController extends ApiController
{
    public function expenseAllMonthly(Request $request)
    {
        $loggedUserEmployee = EmployeeModel::find($request->Employee);
        $loggedUserPosition = EmployeePositionModel::where(['EmployeeID' => $loggedUserEmployee->Id, 'Active' => 2])->first();
        $employeesAtRegion = EmployeePositionModel::where(['RegionID' => $loggedUserPosition->RegionID, 'Active' => 2])->get();
        $totalPrice = 0;
        $employeesAtRegionIDs = [];

        foreach ($employeesAtRegion as $item) {
            array_push($employeesAtRegionIDs, $item->EmployeeID);
        }

        $expenseQ = ExpenseModel::select("Expense.*", DB::raw("SUM(ExpenseDocumentElement.amount) AS price, MONTH(Expense.created_date) as monthId"))
            ->leftJoin("ExpenseDocument", "ExpenseDocument.expense_id", "=", "Expense.id")
            ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
            ->where(['Expense.active' => 1])
            ->where(function ($query) use ($employeesAtRegionIDs) {
                $query->whereIn('EmployeeID', $employeesAtRegionIDs);
            });

        if (isset($request->month) && $request->month != null) {
            $expenseQ->whereRaw('MONTH(Expense.created_date) = ' . $request->month);
            $expenseQ->whereRaw('YEAR(Expense.created_date) = ' . $request->year);
            $totalPrice = $expenseQ->sum('price');
            $expenseQ->groupBy("Expense.id")->orderBy('Expense.created_date', "DESC");
        } else if ($request->year) {
                $expenseQ->whereRaw('YEAR(Expense.created_date) = ' . $request->year);
                $expenseQ->groupBy("monthId")->orderBy('monthId', "DESC");

        }

        $expenses = $expenseQ->get();

        return response([
            'status'        => true,
            'message'       => '????lem Ba??ar??l??',
            'data'          => $expenses,
            'totalPrice'    => $totalPrice
        ], 200);

    }

    public function expenseList(Request $request)
    {
        $status = ($request->input("status") === false) ? "" : $request->input("status");
        $employee = EmployeeModel::find($request->Employee);
        $expenseQ = ExpenseModel::select("Expense.*", DB::raw("SUM(ExpenseDocumentElement.amount) AS price"))
            ->leftJoin("ExpenseDocument", "ExpenseDocument.expense_id", "=", "Expense.id")
            ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
            ->where(["Expense.active" => 1, "Expense.EmployeeID" => $request->Employee])
            ->groupBy("Expense.id")->orderBy("Expense.created_date", "DESC");



        switch ($status) {

            case 0:
                $expenseQ->where(["Expense.status" => $status]);
                break;
            case 1:
                if ($request->approveStatus == 2)
                {
                    $expenseQ->where(["Expense.reject_status" => 1]);
                    $expenseQ->where(["Expense.status" => $status]);
                }
                elseif ($request->approveStatus == 1)
                {
                    $expenseQ->where(["Expense.status" => $status + 1]);
                    $expenseQ->where(["ExpenseDocument.manager_status" => $request->approveStatus]);
                    $expenseQ->where(["Expense.reject_status" => 0]);
                }
                else{
                    $expenseQ->where(["Expense.status" => $status]);
                    $expenseQ->where(["ExpenseDocument.manager_status" => $request->approveStatus]);
                    $expenseQ->where(["Expense.reject_status" => 0]);
                }
                break;
            case 2:
                if ($request->approveStatus == 2)
                {
                    $expenseQ->where(["Expense.reject_status" => 1]);
                    $expenseQ->where(["Expense.status" => $status]);
                }
                elseif ($request->approveStatus == 1)
                {
                    $expenseQ->where(["Expense.status" => $status + 1]);
                    $expenseQ->where(["ExpenseDocument.pm_status" => $request->approveStatus]);
                    $expenseQ->where(["Expense.reject_status" => 0]);
                }
                else{
                    $expenseQ->where(["Expense.status" => $status]);
                    $expenseQ->where(["ExpenseDocument.pm_status" => $request->approveStatus]);
                    $expenseQ->where(["Expense.reject_status" => 0]);
                }
                break;
            case 3:
                if ($request->approveStatus == 2)
                {
                    $expenseQ->where(["Expense.reject_status" => 1]);
                    $expenseQ->where(["Expense.status" => $status]);
                }
                elseif ($request->approveStatus == 1)
                {
                    $expenseQ->where(["Expense.status" => $status + 1]);
                    $expenseQ->where(["ExpenseDocument.accounting_status" => $request->approveStatus]);
                    $expenseQ->where(["Expense.reject_status" => 0]);
                }
                else{
                    $expenseQ->where(["Expense.status" => $status]);
                    $expenseQ->where(["ExpenseDocument.accounting_status" => $request->approveStatus]);
                    $expenseQ->where(["Expense.reject_status" => 0]);
                }
                break;
             case 4:
                 $expenseQ->where(["Expense.status" => $status]);
                 break;

        }



        //$data["manager"] = $user->user_property->manager;
        $data["expenses"] = $expenseQ->get();

        for ($i = 0; $i < 2; $i++) {
            $Query = "";
            if ($i == 0) {
                $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%' and EMAIL= :email", ["email" => $employee->JobEmail]);
                if (count($CariKod) > 0)
                    $PersonelCariKodu["Is"] = $CariKod[0]->CARI_KOD;
            } elseif ($i == 1) {
                $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'PS%' and EMAIL= :email", ["email" => $employee->JobEmail]);
                if (count($CariKod) > 0)
                    $PersonelCariKodu["Seyahat"] = $CariKod[0]->CARI_KOD;
                else
                    $PersonelCariKodu["Seyahat"] = "0";
            }
        }
        foreach ($PersonelCariKodu as $key => $value) {
            $data["Tutar"][$key] = 0;
            $AvansOzet = DB::connection('sqlsrvn')->select("SELECT * FROM ArnVw_PersonelAvansOzet WHERE CARI_KOD= :carikod", ["carikod" => $value]);
            if (count($AvansOzet) > 0) {
                if ($AvansOzet[0]->BAKIYE == ".00000000")
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
        $employee = EmployeeModel::find($request->Employee);
        $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%' and EMAIL= :email", ["email" => $employee->JobEmail]);
        if (count($CariKod) > 0)
            $PersonelCariKodu["Is"] = $CariKod[0]->CARI_KOD;
        else
            $PersonelCariKodu["Is"] = "0";


        $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE CARI_KOD LIKE 'PS%' and EMAIL= :email", ["email" => $employee->JobEmail]);
        if (count($CariKod) > 0)
            $PersonelCariKodu["Seyahat"] = $CariKod[0]->CARI_KOD;
        else
            $PersonelCariKodu["Seyahat"] = "0";


        foreach ($PersonelCariKodu as $key => $value) {
            $data[$key] = 0;
            $AvansOzet = DB::connection('sqlsrvn')->select("SELECT * FROM ArnVw_PersonelAvansOzet WHERE CARI_KOD= :carikod", ["carikod" => $value]);
            if (count($AvansOzet) > 0) {
                if ($AvansOzet[0]->BAKIYE == ".00000000")
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
        $p = json_decode($client->get($this->crm_url . "projeler/projeno/" . $ProjectCode)->getBody());
        $replace = array('<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">', '</string>');
        $projeler = json_decode(str_replace($replace, "", $p), true);
        if (!$projeler) {
            $CrmExternalProjectQ = AsayProjeModel::where(["PROJE_KODU" => $ProjectCode]);
            if ($CrmExternalProjectQ->count() > 0) {
                $CrmExternalProject = $CrmExternalProjectQ->first();
                $projeler = array();
                $projeler[0]["ownerusercode"] = $CrmExternalProject->PLASIYER;
                $projeler[0]["projectno"] = $CrmExternalProject->PROJE_KODU;
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
        $userEmployee = EmployeeModel::find($request->Employee);
        $post["type"] = $request->input("type");
        $post["name"] = $request->input("name");
        $post["expense_type"] = $request->input("expense_type");
        $post["project_id"] = $request->input("project_id");
        $post["category_id"] = $request->input("category_id") !== null ? $request->input("category_id") : "";
        $post['code'] = $request->input('code');
        $post["description"] = $request->input("description");
        $post["expense_id"] = $request->input("expense_id");
        $expense_id = $post["expense_id"];
        if ($post["type"] == "onay") {
            $expenseDocument = ExpenseDocumentModel::where(["expense_id" => $expense_id, "active" => 1]);
            if ($expenseDocument->count() == 0) {
                return response([
                    'status' => false,
                    'message' => "Masrafa Belge Eklemeden Onaya G??nderilemez."
                ], 200);
            }
            $documentCari = ExpenseDocumentModel::where(["expense_id" => $expense_id, "active" => 1, "document_type" => "Fatural??"])
                ->where(function ($query) {
                    $query->where('netsis_carikod', '=', "0")
                        ->orWhere('netsis_carikod', '=', "")
                        ->orWhere('netsis_carikod', '=', null);
                });
            if ($documentCari->count() > 0) {
                return response([
                    'status' => false,
                    'message' => "Onaya G??ndermek ????in Carisi Olmayan Belgeleri D??zenleyiniz."
                ], 200);
            }
        }


        if ($expense_id == "")
            $AsayExpense = new ExpenseModel();
        else
            $AsayExpense = ExpenseModel::find($expense_id);

        $AsayExpense->name = $post["name"];
        $AsayExpense->project_id = $post["project_id"] !== null ? $post["project_id"] : "";
        $AsayExpense->category_id = $post["category_id"] !== null ? $post["category_id"] : "";
        $AsayExpense->expense_type = $post["expense_type"];
        $AsayExpense->code = $post['code'];
        $AsayExpense->description = $post["description"];
        $AsayExpense->EmployeeID = $request->Employee;
        if ($post["type"] == "kaydet") {
            $AsayExpense->status = 0;
        } else if ($post["type"] == "onay") {
            ExpenseModel::sendMailToManager($request);
            NotificationsModel::saveNotification($AsayExpense->EmployeeManager->Id,1,$AsayExpense->id,$AsayExpense->name,"Harcama talebi i??in onay??n??z bekleniyor","expense/edit/".$AsayExpense->id);
            $AsayExpense->status = 1;
        }

        if ($AsayExpense->id == null)
            LogsModel::setLog($request->Employee, $AsayExpense->id, 1, 1, '', '', $AsayExpense->name . ' ba??l??kl?? harcama ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' taraf??ndan olu??turuldu.', '', '', '', '', '');
        //TODO Edit i??in log kayd?? nas??l olu??acak belirlenmelidir.
        if ($AsayExpense->save()) {
            $AsayExpense->fresh();
            if ($post["type"] == "kaydet")
                LogsModel::setLog($request->Employee, $AsayExpense->id, 1, 1, '', '', $AsayExpense->name . ' ba??l??kl?? harcama ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' taraf??ndan olu??turuldu.', '', '', '', '', '');

            return response([
                'status' => true,
                'type' => $post["type"],
                'data' => [
                    "expense_id" => $AsayExpense->id
                ]
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Kay??t Yap??l??rken Hata Olu??tu",
            ], 200);
        }
    }

    public function documentSave(Request $request)
    {
        $documentId = $request->document_id !== null ? $request->document_id : "";
        $expense = null;
        if ($documentId != "") {
            $expenseDocument = ExpenseDocumentModel::find($documentId);
            $expense = ExpenseModel::find($expenseDocument->expense_id);
            if ($expenseDocument === null)
                $expenseDocument = new ExpenseDocumentModel();
        } else {
            $expenseDocument = new ExpenseDocumentModel();
        }
        $expenseDocument->expense_id = $request->expense_id;
        $expenseDocument->cari_name = $request->cari_name;
        $expenseDocument->cari_address = $request->cari_address;
        $expenseDocument->cari_tip = $request->cari_tip;
        $expenseDocument->cari_province = $request->cari_province;
        $expenseDocument->cari_tax_office = $request->cari_tax_office;
        $expenseDocument->cari_tax_number = $request->cari_tax_number;
        $expenseDocument->document_date = $request->document_date;
        $expenseDocument->document_time = $request->document_time;
        $expenseDocument->document_number = $request->document_number;
        $expenseDocument->document_type = $request->document_type;
        $expenseDocument->currency = "00";//TRY Varsay??lan olarak belirlendi
        $expenseDocument->netsis_carikod = $request->netsis_carikod;
        $expenseDocument->active = 1;

        $isAccounter = ProcessesSettingsModel::where(['PropertyCode' => 'Accounter', 'PropertyValue' => $request->Employee, 'object_type' => 1])->get();
        if ($expense != null && ($expense->Project->Manager->Id == $request->Employee || count($isAccounter) > 0 ))
        {
            $dirtyFields = $expenseDocument->getDirty();
            foreach ($dirtyFields as $field => $newdata) {
                $olddata = $expenseDocument->getOriginal($field);
                if ($olddata != $newdata) {
                    $expenseDocument->{$field.'_old'} = $olddata;
                    $expenseDocument->{$field} = $newdata;
                }
            }
        }

        $result = $expenseDocument->save();

        if ($result && $request->hasFile('expense_document_file')) {
            $file = file_get_contents($request->expense_document_file->path());
            $guzzleParams = [

                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'ExpenseDocumentDoc_' . $expenseDocument->id . '.' . $request->expense_document_file->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'expensedocument'
                    ],
                    [
                        'name' => 'token',
                        'contents' => $request->token
                    ]

                ],
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $expenseDocument->file = $responseBody->data;
                $expenseDocument->save();
            }


            return response([
                'status' => true,
                'message' => "Kay??t Ba??ar??l??",
                'data' => ExpenseDocumentModel::find($expenseDocument->id)
            ], 200);

        } else if ($result)
            return response([
                'status' => true,
                'message' => "Kay??t Ba??ar??l??",
                'data' => ExpenseDocumentModel::find($expenseDocument->id)
            ], 200);


        else
            return response([
                'status' => false,
                'message' => "????lem Ba??ar??s??z",
            ], 200);

    }

    public function documentElementSave(Request $request)
    {
        $expense = null;
        if (!isset($request->documentId) || $request->documentId == '' || $request->documentId == null)
            return response([
                'status' => false,
                'message' => 'Kalemin hangi belgeye eklenece??i belli de??il'
            ], 200);
        else{
            $expenseID = ExpenseDocumentModel::find($request->documentId)->expense_id;
            $expense = ExpenseModel::find($expenseID);
        }



        if (!isset($request->elementId) || $request->elementId == '' || $request->elementId == null)
            $documentElement = new ExpenseDocumentElementModel();
        else
        {
            $documentElement = ExpenseDocumentElementModel::find($request->elementId);
        }




        $requestArray = $request->all();
        $documentElement->document_id = $request->documentId;
        $documentElement->expense_account = $requestArray['expense_account'];
        $documentElement->car_plate = isset($requestArray['carPlate']) ? $requestArray['carPlate'] : null;
        $documentElement->content = $requestArray['content'];
        $documentElement->quantity = $requestArray['quantity'];
        $documentElement->kdv = $requestArray['kdv'];
        $documentElement->price = $requestArray['price'];
        $documentElement->amount = number_format($requestArray['amount'] , 2, '.', '');
        $documentElement->active = 1;

        $isAccounter = ProcessesSettingsModel::where(['PropertyCode' => 'Accounter', 'PropertyValue' => $request->Employee, 'object_type' => 1])->get();
        if ($expense != null && ($expense->Project->Manager->Id == $request->Employee || count($isAccounter) > 0 ))
        {
            $dirtyFields = $documentElement->getDirty();
            foreach ($dirtyFields as $field => $newdata) {
                $olddata = $documentElement->getOriginal($field);
                if ($olddata != $newdata) {
                    $documentElement->{$field.'_old'} = $olddata;
                    $documentElement->{$field} = $newdata;
                }
            }
        }


        $result = $documentElement->save();

        if ($result)
            return response([
                'status' => true,
                'message' => 'Kay??t Ba??ar??l??',
                'data' => $documentElement
            ], 200);
        else
            return response([
                'status' => false,
                'message' => '????lem Ba??ar??s??z'
            ], 200);

    }

    public function expenseAuthority($asayExpense, $EmployeeID)
    {
        $status = false;
        if ($asayExpense->status == 1) {
            $employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $asayExpense->EmployeeID])->first();
            if ($employeePosition->ManagerID == $EmployeeID)
                $status = true;
        } else if ($asayExpense->status == 2) {
            if ($asayExpense->category_id <> "") {
                $projetCategories = ProjectCategoriesModel::find($asayExpense->category_id);
                if ($EmployeeID == $projetCategories->manager_id)
                    $status = true;
            } else {
                $project = ProjectsModel::find($asayExpense->project_id);
                if ($EmployeeID == $project->manager_id)
                    $status = true;
            }

        } else if ($asayExpense->status == 3 || $asayExpense->status == 4) {
            //TODO arge userlar?? yap??ld?? ??imdilik sonras??nda muhasebe onaylat??c?? grup id ile de??i??tirilecek
            $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID" => $EmployeeID, 'active' => 1])->whereIn("group_id",[11,12])->count();
            $processSettingExpenseAccounter = ProcessesSettingsModel::where(['object_type' => 1,'PropertyValue' => $EmployeeID,'PropertyCode' => 'Accounter'])->count();
            if ($userGroupCount > 0 || $processSettingExpenseAccounter > 0)
                $status = true;
        }
        return $status;
    }

    public function getExpense(Request $request)
    {
        $expenseId = $request->input("expense_id");
        $asayExpense = ExpenseModel::find($expenseId);

        if ($asayExpense === null) {
            return response([
                'status' => false,
                'message' => "Harcama Belgesi Bulunamad??"
            ], 200);
        } else if ($asayExpense->EmployeeID == $request->Employee) {
            return response([
                'status' => true,
                'data' => $asayExpense
            ], 200);
        } else {
            $status = self::expenseAuthority($asayExpense, $request->Employee);
            if ($status == false) {
                return response([
                    'status' => false,
                    'message' => "Yetkisiz ????lem"
                ], 200);
            } else {
                return response([
                    'status' => true,
                    'data' => $asayExpense
                ], 200);
            }
        }
    }

    public function getExpenseDocument(Request $request)
    {
        $documentId = $request->input("document_id");
        $asayExpenseDocument = ExpenseDocumentModel::find($documentId);


        if ($asayExpenseDocument === null) {
            return response([
                'status' => false,
                'message' => "Belge Bulunamad??"
            ], 200);
        } else {
            $cariProvince = $asayExpenseDocument->cari_province != null ? TaxOfficesModel::where('code', $asayExpenseDocument->cari_province)->first() : null;
            $cariTaxOffice = $asayExpenseDocument->cari_tax_office != null ? TaxOfficesModel::where('code', $asayExpenseDocument->cari_tax_office)->first() : null;
            $asayExpenseDocument->CariProvince = $cariProvince;
            $asayExpenseDocument->TaxOffice = $cariTaxOffice;
            $asayExpense = ExpenseModel::find($asayExpenseDocument->expense_id);
            $documentElement = ExpenseDocumentElementModel::where(["document_id" => $asayExpenseDocument->id, "active" => 1])->get();
            if ($asayExpense->EmployeeID == $request->Employee) {
                return response([
                    'status' => true,
                    'data' =>
                        [
                            "document" => $asayExpenseDocument,
                            "documentElement" => $documentElement
                        ]
                ], 200);
            } else {
                //$status = self::expenseAuthority($asayExpense, $request->Employee);
                $status = true;
                if ($status == false) {
                    return response([
                        'status' => false,
                        'message' => "Yetkisiz ????lem"
                    ], 200);
                } else {
                    return response([
                        'status' => true,
                        'data' =>
                            [
                                "document" => $asayExpenseDocument,
                                "documentElement" => $documentElement
                            ]
                    ], 200);
                }
            }
        }
    }

    public function expenseDocumentList(Request $request)
    {
        $expenseId = $request->input("expense_id");
        $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*", DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
            ->where(["ExpenseDocument.active" => 1, "ExpenseDocument.expense_id" => $expenseId])
            ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
            ->leftJoin("Expense", "ExpenseDocument.expense_id", "=", "Expense.id")
            ->groupBy("ExpenseDocument.id");
        $SumTutar = 0;
        if ($expenseDocumentsQ->count() > 0) {
            $expenseDocuments = $expenseDocumentsQ->get();
            foreach ($expenseDocuments as $expenseDocument) {
                $SumTutar = 0;
                if ($expenseDocument->cari_tip == 1) {
                    $Cari = AsayCariModel::find($expenseDocument->netsis_carikod);
                    $expenseDocument->CARI_ISIM = $Cari->CariIsim;
                } elseif ($expenseDocument->document_type == "Fatural??" && $expenseDocument->cari_tip <> 1) {
                    $CariIsim = DB::connection('sqlsrvn')->select("SELECT CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM FROM TBLCASABIT WHERE CARI_KOD= :cari_kod", ["cari_kod" => $expenseDocument->CARIKOD]);
                    if (count($CariIsim) > 0) {
                        $expenseDocument->CARI_ISIM = $CariIsim[0]->CARI_ISIM;
                    } else {
                        $expenseDocument->CARI_ISIM = "";
                    }
                } else {
                    $expenseDocument->CARI_ISIM = "";
                }

                $expenseDocumentElements = ExpenseDocumentElementModel::where(['document_id' => $expenseDocument->id,'active' => 1])->count();
                $expenseDocument->elementsCount = $expenseDocumentElements;

                $nonActiveElements = ExpenseDocumentElementModel::where(['document_id' => $expenseDocument->id,'active' => 0])->get();
                $substractTutar = 0;
                foreach ($nonActiveElements as $nonActiveElement)
                {
                    $substractTutar+=$nonActiveElement->amount;
                }
                $expenseDocument->TTUTAR -=$substractTutar;
                $SumTutar += $expenseDocument->TTUTAR;
                $expenseDocument->TTUTAR = ($expenseDocument->TTUTAR .' TL');
            }
        } else {
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
        array_unshift($ParaQ, $parabirimleri);

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
        $currentId = $request->CariID !== null ? $request->CariID : "";
        if ($currentId != "") {
            $expenseCurrentCount = AsayCariModel::where(["id" => $currentId])->count();
            if ($expenseCurrentCount > 0)
                $expenseCurrent = AsayCariModel::find($currentId);
            else
                $expenseCurrent = new AsayCariModel();
        } else {
            $expenseCurrent = new AsayCariModel();
        }
        $expenseCurrent->CariIsim = $request->CariIsim;
        $expenseCurrent->CariUlkeKodu = $request->CariUlkeKodu;
        $expenseCurrent->CariIl = $request->CariIl;
        $expenseCurrent->CariIlce = $request->CariIlce;
        $expenseCurrent->CariAdres = $request->CariAdres;
        $expenseCurrent->CariVergiDairesi = $request->CariVergiDairesi;
        $expenseCurrent->CariVergiNo = $request->CariVergiNo;
        $expenseCurrent->CariTelefon = $request->CariTelefon;
        $expenseCurrent->CariFax = $request->CariFax;
        $expenseCurrent->Netsis = 0;
        if ($expenseCurrent->save()) {
            return response([
                'status' => true,
                'data' => ["cariId" => $expenseCurrent->ID]
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Kay??t Yap??l??rken Hata Olu??tu",
            ], 200);
        }
    }

    public function listTaxOffice(Request $request)
    {
        $parent = $request->parent !== null ? $request->parent : 0;
        $taxOffices = TaxOfficesModel::where(["parent" => $parent])->get();

        return response([
            'status' => false,
            'data' => $taxOffices
        ], 200);
    }

    public function getCurrent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'taxNumber' => 'required',
            'taxCityCode' => 'required',
            'taxOfficeCode' => 'required',
        ]);
        if ($validator->fails()) {
            return response([
                "status" => false,
                "message" => $validator->messages()], 200);
        }

        $taxNumber = $request->taxNumber;
        $taxCityCode = $request->taxCityCode;
        $taxOfficeCode = $request->taxOfficeCode;

        $taxCity = TaxOfficesModel::where(["code" => $taxCityCode])->first();
        $taxOffice = TaxOfficesModel::where(["code" => $taxOfficeCode])->first();

        $cariler = [];
        $currents = [];
        $currents = DB::connection('sqlsrvn')->select("SELECT CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM,CARI_TEL,dbo.TRK2(CARI_IL) AS CARI_IL,dbo.TRK2(CARI_ILCE) AS CARI_ILCE,dbo.TRK2(CARI_ADRES) AS CARI_ADRES,dbo.TRK2(VERGI_DAIRESI) AS VERGI_DAIRESI,VERGI_NUMARASI FROM TBLCASABIT WHERE VERGI_DAIRESI= :vergi_dairesi AND VERGI_NUMARASI= :vergi_numarasi", ["vergi_dairesi" => $taxOffice->name, "vergi_numarasi" => $taxNumber]);
        if (count($currents) > 0) {
            $currents[0]->netsis = 1;
            $cariler = $currents[0];
        } else {
            $sendData = [
                'body' => http_build_query([
                    "cmd" => "vergiLevhasiDetay_sorgula",
                    "callid" => "434fda15eda09-10",
                    "pageName" => "P_INTVRG_INTVD_E_VERGI_LEVHA_SORGULA",
                    "token" => "d1078f5e3dc646b78d5d4e5842f21e97feb48d366bc7617458b6679dec12675154a01fccc42292bb04d926bc259dbc75e39dd8e202535fd70a7098396c74a6f7",
                    "jp" => '{"sorgulayanTckn":"","sorgulanacakVkn":"' . $taxNumber . '","sorgulanacakTckn":"","sorgulanacakVDIl":"' . $taxCityCode . '","sorgulanacakVDAd":"' . $taxOfficeCode . '","islemTip":"0"}'
                ]),
                'headers' => [
                    'Content-Type' => "application/x-www-form-urlencoded; charset=UTF-8",
                ]
            ];

            $client = new \GuzzleHttp\Client();
            $current = json_decode($client->post("https://ivd.gib.gov.tr/tvd_server/dispatch", $sendData)->getBody());

            if (count((array)$current->data) == 0)
                return response([
                    'status' => false,
                    'message' => 'Vergi Dairesi Kay??tlar??nda Vergi Numaras?? Bulunamad??. Muhasebe Departman?? ??le ??leti??ime Ge??iniz.'
                ], 200);

            $cariler = [
                "CARI_KOD" => "",
                "CARI_ISIM" => $current->data->unvan,
                "CARI_TEL" => "",
                "CARI_IL" => "",
                "CARI_ILCE" => "",
                "CARI_ADRES" => $current->data->adres,
                "VERGI_DAIRESI" => $taxOffice->name,
                "VERGI_NUMARASI" => $taxNumber,
                "netsis" => 0
            ];
        }

        return response([
            'status' => true,
            'data' => $cariler,
        ], 200);
    }

    public function listNetsisCurrent(Request $request)
    {
        //Servis kapat??ld?? #serkan
        exit;
        $netsisCariKod = $request->input("netsisCariKod") !== null ? $request->input("netsisCariKod") : "";
        if ($netsisCariKod != "") {
            return response([
                'status' => true,
            ], 200);
            $cariTip = $request->input("cariTip") !== null ? $request->input("cariTip") : "";
            $cariler = [];
            if ($cariTip == 0) {
                $currents = DB::connection('sqlsrvn')->select("SELECT CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM FROM TBLCASABIT WHERE CARI_KOD= :cari_kod", ["cari_kod" => $netsisCariKod]);
                foreach ($currents as $current) {
                    $current->netsis = 1;
                    $cariler[] = $current;
                }
            } else {
                $currents = AsayCariModel::where(["id" => $netsisCariKod])->get();
                foreach ($currents as $current) {
                    $current->netsis = 0;
                    $cariler[] = $current;
                }
            }
        } else {
            $search = $request->input("search");
            $cariler = [];

            $currents = DB::connection('sqlsrvn')->select("SELECT TOP 10 CARI_KOD,dbo.TRK2(CARI_ISIM) as CARI_ISIM FROM TBLCASABIT WHERE (CARI_KOD LIKE 'D%' OR (CARI_KOD LIKE 'S%' AND CARI_KOD NOT LIKE 'S%D' AND CARI_KOD NOT LIKE 'S%E')) AND CARI_ISIM LIKE N'%" . $search . "%'");
            foreach ($currents as $current) {
                $current->netsis = 1;
                $cariler[] = $current;
            }

            $currents = AsayCariModel::where(["Netsis" => 0])->where('CariIsim', 'like', '%' . $search . '%')->get();
            foreach ($currents as $current) {
                $current->netsis = 0;
                $cariler[] = $current;
            }
        }

        $array = [];
        foreach ($cariler as $key => $cari) {
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

        $status = ($request->input("status") !== null) ? $request->input("status") : "";
        $singleStatus = $request->input("singleStatus");
        $status = $status == "0" ? "1" : $status;
        $employeeManagers = EmployeePositionModel::where(["Active" => 2, "ManagerId" => $request->Employee])->pluck("EmployeeID");
        $projects = ProjectsModel::where(["manager_id" => $request->Employee])->pluck("id");
        $categories = ProjectCategoriesModel::where(["manager_id" => $request->Employee])->pluck("id");


        $expenseQ = ExpenseModel::select("Expense.*", DB::raw("SUM(ExpenseDocumentElement.price) AS price"))
            ->leftJoin("ExpenseDocument", "ExpenseDocument.expense_id", "=", "Expense.id")
            ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
            ->where(["Expense.active" => 1]);



        $expenseQ->where(function ($query) use ($projects, $categories, $employeeManagers, $status) {
            if (count($projects) > 0 && ($status == 2 || $status == ""))
                $query->whereIn("project_id", $projects);
            if (count($categories) > 0 && ($status == 2 || $status == ""))
                $query->whereIn("category_id", $categories, "OR");
            if (count($employeeManagers) > 0 && ($status == 1 || $status == ""))
                $query->whereIn("EmployeeID", $employeeManagers, "OR");
        });
        $expenseQ->groupBy("Expense.id")->orderBy("Expense.created_date", "DESC");
        if ($status == 3)
            $statusArray = [3];
        else if ($status == 4) {
            $statusArray = [4];
            $expenseQ->where(['netsis' => 1]);
        } else if ($status == 1 && $singleStatus == 1)
            $statusArray = [1];
        else if ($status == 1 && $singleStatus == 0)
            $statusArray = [2, 3, 4];
        else if ($status == 2 && $singleStatus == 1)
            $statusArray = [2];
        else if ($status == 2 && $singleStatus == 0)
            $statusArray = [3, 4];

        $expenseQ->whereIn("Expense.status", $statusArray);
        $data = $expenseQ->get();

        $data2 = [];

        foreach ($data as $item)
            array_push($data2,$item);

        foreach ($data2 as $key => $item)
        {
            $expenseDocuments = ExpenseDocumentModel::where(['active' => 1,'expense_id'=>$item->id])->get();
            $rejectedCount = 0;
            foreach ($expenseDocuments as $expenseDocument)
            {
                if($expenseDocument->manager_status == 2 || $expenseDocument->pm_status == 2 || $expenseDocument->accounting_status == 2)
                {
                    $rejectedCount++;
                }
            }

            if($request->rejectedStatus == 1)//T??m Belgeleri Reddedilen harcamalar?? ??ekiyoruz
            {
                if (count($expenseDocuments) != $rejectedCount)//Bu Belgede reddedilmeyen kay??tlar var demek bunlar?? ????kar??yoruz
                {
                    array_splice($data2,array_search($item,$data2),1);
                }
                else{
                    $item->rejected = true;
                }

            }
            else{//T??m Belgeleri Reddedilmemi?? harcamalar?? ??ekiyoruz
                if (count($expenseDocuments) == $rejectedCount)
                {
                    array_splice($data2,array_search($item,$data2),1);
                }
            }

        }


        return response([
            'status' => true,
            'data' => $data2,
            'employee_managers' => $employeeManagers
        ], 200);
    }

    public function expensePendingListCount(Request $request)
    {
        $expenseQ = ExpenseModel::where(["Expense.active" => 1])->groupBy('status');

        $counts = $expenseQ->get();
        return response([
            'status' => true,
            'message' => '????lem Ba??ar??l??',
            'data' => $counts
        ],200);
    }

    public function expenseDelete(Request $request)
    {
        $expenseId = $request->expenseId;
        if ($expenseId === null) {
            return response([
                'status' => false,
                'message' => "Masraf Id Bo?? Olamaz"
            ], 200);
        }
        $expenseQ = ExpenseModel::where(["id" => $expenseId, "EmployeeID" => $request->Employee, "status" => 0]);
        if ($expenseQ->count() == 0) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }
        $expenseQ->update(["active" => 0]);
        $expense = $expenseQ->first();

        $documentsQ = ExpenseDocumentModel::where(["expense_id" => $expenseId]);
        foreach ($documentsQ->get() as $document) {
            ExpenseDocumentElementModel::where(["document_id" => $document->id])->update(["active" => 0]);
        }
        $documentsQ->update(["active" => 0]);

        $creatorOfExpense = EmployeeModel::find($expense->EmployeeID);
        LogsModel::setLog($request->Employee, $expenseId, 1, 2, '', '', $expense->name . ' ba??l??kl?? harcama ' . $creatorOfExpense->UsageName . '' . $creatorOfExpense->LastName . ' taraf??ndan silindi.', '', '', '', '', '');

        return response([
            'status' => true,
            'message' => "Kay??t Silindi"
        ], 200);
    }

    public function deleteDocument(Request $request)
    {
        $documentId = $request->documentId;
        if ($documentId === null) {
            return response([
                'status' => false,
                'message' => "Belge Id Bo?? Olamaz"
            ], 200);
        }


        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);
        if ($expense->EmployeeID != $request->Employee) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }
        ExpenseDocumentElementModel::where(["document_id" => $documentId])->update(["active" => 0]);
        $document->active = 0;
        $documentResult = $document->save();
        if ($documentResult) {
            return response([
                'status' => true,
                'message' => "Belge Silme Ba??ar??l??"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Silme Ba??ar??s??z"
            ], 200);
        }

    }

    public function deleteElement(Request $request)
    {
        $elementId = $request->elementId;
        if ($elementId === null) {
            return response([
                'status' => false,
                'message' => "Kalem Id Bo?? Olamaz"
            ], 200);
        }
        $documentId = $request->documentId;
        if ($documentId === null) {
            return response([
                'status' => false,
                'message' => "Belge Id Bo?? Olamaz"
            ], 200);
        }

        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);
        if ($expense->EmployeeID != $request->Employee) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }
        $updateResult = ExpenseDocumentElementModel::where(["document_id" => $documentId])->update(["active" => 0]);
        if ($updateResult) {
            return response([
                'status' => true,
                'message' => "Kalem Silme Ba??ar??l??"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Silme Ba??ar??s??z"
            ], 200);
        }

    }

    public function userTakeBack(Request $request)
    {
        $expenseId = $request->expenseId;
        if ($expenseId === null) {
            return response([
                'status' => false,
                'message' => "Masraf Id Bo?? Olamaz"
            ], 200);
        }

        $expenseQ = ExpenseModel::where(["id" => $expenseId, "EmployeeID" => $request->Employee, "active" => 1])->where("status", "<=", 1);
        if ($expenseQ->count() == 0) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }
        $expense = $expenseQ->update(["status" => 0]);

        $documentsQ = ExpenseDocumentModel::where(["expense_id" => $expenseId]);
        $documentsQ->update(["manager_status" => 0, "pm_status" => 0, "accounting_status" => 0]);
        return response([
            'status' => true,
            'message' => "Geri Al??nd??"
        ], 200);
    }

    public function expenseDocumentConfirm(Request $request)
    {
        $documentId = $request->documentId;
        if ($documentId === null) {
            return response([
                'status' => false,
                'message' => "Belge Id Bo?? Olamaz"
            ], 200);
        }
        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);
        $status = self::expenseAuthority($expense, $request->Employee);
        if ($status == false) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }

        if ($request->confirm == 1)
            $confirm = 1;
        else {
            $confirm = 2;
            $document->netsis = 2;
        }

        if ($expense->status == 1) {
            $document->manager_status = $confirm;
            if ($confirm == 2) {
                $document->reject_reason = $request->rejectReason;
                $expenseDocumentsQ = ExpenseDocumentModel::where(['active' => 1, 'expense_id' => $expense->id])->whereNotIn("id",[$documentId]);
                $expenseDocuments = $expenseDocumentsQ->get();
                $rejectedCount = 0;
                foreach ($expenseDocuments as $expenseDocument)
                {
                    if ($expenseDocument->manager_status == 2)
                        $rejectedCount++;
                }
                if ($rejectedCount == $expenseDocumentsQ->count())
                {
                    $expense->reject_status = 1;
                    $expense->save();
                }

                $document->accounting_status = 2;
                $document->pm_status = 2;
            }
        } else if ($expense->status == 2) {
            $document->pm_status = $confirm;
            if ($confirm == 2) {
                $document->reject_reason = $request->rejectReason;
                $expenseDocumentsQ = ExpenseDocumentModel::where(['active' => 1, 'expense_id' => $expense->id])->whereNotIn("id",[$documentId]);
                $expenseDocuments = $expenseDocumentsQ->get();
                $rejectedCount = 0;
                foreach ($expenseDocuments as $expenseDocument)
                {
                    if ($expenseDocument->pm_status == 2)
                        $rejectedCount++;
                }
                if ($rejectedCount == $expenseDocumentsQ->count())
                {
                    $expense->reject_status = 1;
                    $expense->save();
                }


                $document->accounting_status = 2;
            }
        } else if ($expense->status == 3)
        {
            $document->accounting_status = $confirm;
            if ($confirm == 2)
            {
                $document->reject_reason = $request->rejectReason;
                $expenseDocumentsQ = ExpenseDocumentModel::where(['active' => 1, 'expense_id' => $expense->id])->whereNotIn("id",[$documentId]);
                $expenseDocuments = $expenseDocumentsQ->get();
                $rejectedCount = 0;
                foreach ($expenseDocuments as $expenseDocument)
                {
                    if ($expenseDocument->accounting_status == 2)
                        $rejectedCount++;
                }
                if ($rejectedCount == $expenseDocumentsQ->count())
                {
                    $expense->reject_status = 1;
                    $expense->save();
                }
            }

        }


        $documentResult = $document->save();
        if ($documentResult) {
            return response([
                'status' => true,
                'message' => "Onay Ba??ar??l??"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Onaylama Ba??ar??s??z"
            ], 200);
        }

    }

    public function documentConfirmTakeBack(Request $request)
    {
        $documentId = $request->documentId;
        if ($documentId === null) {
            return response([
                'status' => false,
                'message' => "Belge Id Bo?? Olamaz"
            ], 200);
        }
        $document = ExpenseDocumentModel::find($documentId);
        $expense = ExpenseModel::find($document->expense_id);

        $status = self::expenseAuthority($expense, $request->Employee);
        if ($status == false) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }

        if ($expense->status == 1) {
            $document->manager_status = 0;
            $document->pm_status = 0;
            $document->accounting_status = 0;
            $document->netsis = 0;
        } else if ($expense->status == 2) {
            $document->pm_status = 0;
            $document->accounting_status = 0;
            $document->netsis = 0;
        } else if ($expense->status == 3) {
            $document->accounting_status = 0;
            $document->netsis = 0;
        }
        $documentResult = $document->save();

        if ($documentResult) {
            return response([
                'status' => true,
                'message' => "Geri Alma Ba??ar??l??"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Geri Alma Ba??ar??s??z"
            ], 200);
        }

    }

    public function expenseComplete(Request $request)
    {
        $expenseId = $request->expenseId;
        if ($expenseId === null) {
            return response([
                'status' => false,
                'message' => "Masraf Id Bo?? Olamaz"
            ], 200);
        }
        $expense = ExpenseModel::find($expenseId);
        $loggedUserEmployee = EmployeeModel::find($request->Employee);
        $status = self::expenseAuthority($expense, $request->Employee);
        if ($status == false) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }

        if ($expense->status == 1)
            $column = "manager_status";
        else if ($expense->status == 2)
            $column = "pm_status";
        $expenseQ = ExpenseModel::where(["id" => $expenseId, "active" => 1])->whereIn("status", [$expense->status]);
        $expenseDocumentCount = ExpenseDocumentModel::where(["expense_id" => $expenseId, "active" => 1, $column => 0])->count();

        if ($expenseQ->count() == 0 || $expenseDocumentCount > 0) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }
        if ($column == "manager_status") {
            $expenseResult = $expenseQ->update(["status" => 2]);
            ExpenseModel::sendMailToProjectManager($request);
            NotificationsModel::saveNotification($expense->Category->manager_id !== null ? $expense->Category->manager_id : $expense->Project->manager_id,1,$expense->id,$expense->name,"Harcama i??in onay??n??z bekleniyor","expense/edit/".$expense->id);
            NotificationsModel::saveNotification($expense->EmployeeID,1,$expense->id,$expense->name,"Harcama talebiniz, y??neticiniz taraf??ndan onayland??","expense/edit/".$expense->id);
            LogsModel::setLog($loggedUserEmployee->Id, $expenseId, 1, 4, '', '', $expense->name . ' ba??l??kl?? harcama ' . $loggedUserEmployee->UsageName . '' . $loggedUserEmployee->LastName . ' taraf??ndan onayland??.', '', '', '', '', '');
        }

        if ($column == "pm_status") {
            $expenseResult = $expenseQ->update(["status" => 3]);
            ExpenseModel::sendMailToAccounters($request);
            $accounters = ProcessesSettingsModel::where(['PropertyCode' => 'Accounter', 'object_type' => 1])->groupBy('PropertyValue')->pluck("PropertyValue");
            $accounterEmployees = DB::table("Employee")->whereIn("Id",$accounters)->get();
            foreach ($accounterEmployees as $accounterEmployee)
            {
                NotificationsModel::saveNotification($accounterEmployee->JobEmail,1,$expense->id,$expense->name,"Harcama talebi i??in onay??n??z bekleniyor","expense/edit/".$expense->id);
            }
            LogsModel::setLog($loggedUserEmployee->Id, $expenseId, 1, 5, '', '', $expense->name . ' ba??l??kl?? harcama ' . $loggedUserEmployee->UsageName . '' . $loggedUserEmployee->LastName . ' taraf??ndan onayland??.', '', '', '', '', '');
        }

        //TODO Mail g??nderilecek

        if ($expenseResult) {
            return response([
                'status' => true,
                'message' => "Tamamland??"
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => "Tamamlama Ba??ar??s??z"
            ], 200);
        }
    }

    public function SendCurrentToNetsis(Request $request)
    {
        $Cari = new \stdClass();
        $Cari->CariAdres = $request->input("CariAdres");
        $Cari->CariEmail = "";
        $Cari->CariFax = $request->input("CariFax") !== null ? $request->input("CariFax") : "";
        $Cari->CariHesapTipi = "K";
        $Cari->CariIl = $request->input("CariIl") !== null ? $request->input("CariIl") : "";
        $Cari->CariIlce = $request->input("CariIlce") !== null ? $request->input("CariIlce") : "";
        $Cari->CariIsim = $request->input("CariIsim") !== null ? $request->input("CariIsim") : "";
        $Cari->CariKodCRM = "";
        $Cari->CariKodNetsis = "";
        $Cari->CariMusteriTipi = "S";
        $Cari->CariPostaKodu = "";
        $Cari->CariTCKN = $request->input("CariTCKN") !== null ? $request->input("CariIsim") : "";;;
        $Cari->CariTelefon = $request->input("CariTelefon") !== null ? $request->input("CariIsim") : "";;;
        $Cari->CariUlkeKodu = "TR";
        $Cari->CariVergiDairesi = TaxOfficesModel::where(["code" => $request->input("CariVergiDairesi")])->first()->name;
        $Cari->CariVergiNo = $request->input("CariVergiNo");
        $Cari->CariWebAdresi = "";
        $Cari->DovizliCari = "true";
        $Cari->Isletme = "Asay_Iletisim";


        $wsdl = 'http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?wsdl';

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $options = array(
            'uri' => 'http://schemas.xmlsoap.org/wsdl/soap/',
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
            "location" => "http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?singleWsdl",
        );
        try {
            $soap = new SoapClient($wsdl, $options);
            $data = $soap->CariEkle(array("_cari" => $Cari));
        } catch (Exception $e) {
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 200);
        }

        $sonuc["CariKod"] = $data->CariEkleResult->Aciklama;
        $sonuc["Sonuc"] = $data->CariEkleResult->Sonuc;


        if ($sonuc["Sonuc"] == false):
            return response([
                'status' => false,
                'message' => $data->CariEkleResult->Aciklama
            ], 200);
        else:
            return response([
                'status' => true,
                'data' => $sonuc
            ], 200);
        endif;
    }

    public function SendExpenseToNetsis(Request $request)
    {
        $expenseId = $request->input("expenseId");

        //MASRAF DETAYLARI
        $expense = ExpenseModel::find($expenseId);
        $status = self::expenseAuthority($expense, $request->Employee);
        if ($status == false) {
            return response([
                'status' => false,
                'message' => "Yetkisiz ????lem"
            ], 200);
        }

        //Personel bilgileri kontrol ediliyor.
        $employee = EmployeeModel::find($expense->EmployeeID);
        $employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $expense->EmployeeID])->first();
        //$employee = EmployeeModel::find($request->Employee);
        //$employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $request->Employee])->first();
        $company = CompanyModel::find($employeePosition->CompanyID);
        $companyCode = $company->NetsisName;

        if ($expense->expense_type == 1)//???? Avans??
        {
            $Query = "CARI_KOD LIKE 'P%' AND CARI_KOD NOT LIKE 'PS%'";
        } elseif ($expense->expense_type == 2)//Seyahat Avans??
        {
            $Query = "CARI_KOD LIKE 'PS%'";
        }

        //CAR?? KOD ????REN
        $PersonelCariKodu = "";
        $CariKod = DB::connection('sqlsrvn')->select("SELECT CARI_KOD FROM TBLCASABIT WHERE " . $Query . " and EMAIL= :email", ["email" => $employee->JobEmail]);
        if (count($CariKod) > 0)
            $PersonelCariKodu = $CariKod[0]->CARI_KOD;


        if ($PersonelCariKodu == "") {
            $setLog["EXPENSE_ID"] = $expenseId;
            $setLog["LOG"] = "Personel Cari Kodu Netsisde Bulunamad??. Mail adresini kontrol ediniz.<br>Mail Adresi:" . $employee->JobEmail;
            AsayExpenseLogModel::insert($setLog);
            return response([
                'status' => false,
                'message' => $setLog["LOG"]
            ], 200);
        }

        //MASRAF BELGELER??
        $documents = ExpenseDocumentModel::where(["active" => 1, "netsis" => 0, "accounting_status" => 1, "expense_id" => $expenseId])->get();
        if ($documents->count() == 0) {
            return response([
                'status' => false,
                'data' => "Aktar??lacak Belge Bulunamad??"
            ], 200);
        }
        //MASRAF BELGE KALEMLER??
        $document_element = new \stdClass();
        foreach ($documents as $key => $value) {
            $document_elements[$value->id] = ExpenseDocumentElementModel::where(["active" => 1, "document_id" => $value->id])->get();
        }
        $DurumHataSay = 0;

        //Proje kodu ve plasiyer kodu belirleniyor

        $wsdl = 'http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?wsdl';

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $options = array(
            'uri' => 'http://schemas.xmlsoap.org/wsdl/soap/',
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
            "location" => "http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?singleWsdl",
        );
        try {
            $soap = new SoapClient($wsdl, $options);
        } catch (Exception $e) {
            return response([
                'status' => false,
                'data' => $e->getMessage()
            ], 200);
        }
        $project = ProjectsModel::find($expense->project_id);

        foreach ($documents as $key => $value) {
            $MasrafFormu = new \stdClass();
            $MasrafFormu->ProjeKodu = $project->project_code;;
            $MasrafFormu->PlasiyerKodu = $project->plasiyer_code;;
            $Silindi = 0;
            if ($value->document_type == "Fi??li") {
                $MasrafKalem = array();
                $MasrafFormu->Fisli = "E";
                $MasrafFormu->Belgeli = "H";

                $kdvoran = 0;
                $TutarToplam = 0;
                $MatrahToplam = 0;
                foreach ($document_elements[$value->id] as $key2 => $value2) {
                    $KdvOran = $value2->kdv;
                    $Matrah = $value2->price * $value2->quantity;
                    $Adet = $value2->quantity;
                    $Tutar = $value2->amount;

                    $MatrahToplam += $Matrah;
                    $TutarToplam += $Tutar;

                    //kdv dahil ise matrah=tutar
                    $MasrafKalem[0]["Aciklama"] = $value2->content;
                    $MasrafKalem[0]["FisNo"] = $value->document_number;
                    $MasrafKalem[0]["GiderKodu"] = $value2->expense_account;
                    $MasrafKalem[0]["KdvMatrahi"] = $MatrahToplam;
                    $MasrafKalem[0]["KdvOran"] = $KdvOran;
                    $MasrafKalem[0]["Miktar"] = $Adet;
                    $MasrafKalem[0]["Tutar"] = $TutarToplam;
                    /*if($value2["MSTATUS"]==2)
                        $Silindi++;*/
                }
                $CariAciklama = $MasrafKalem[0]["Aciklama"];
            } elseif ($value->document_type == "Fatural??") {
                $MasrafKalem = array();
                $MasrafFormu->Fisli = "H";
                $MasrafFormu->Belgeli = "E";

                foreach ($document_element[$value->id] as $key2 => $value2) {
                    //kdv dahil ise matrah=tutar
                    $MasrafKalem[$key2]["Aciklama"] = $value2->content;
                    $MasrafKalem[$key2]["FisNo"] = $value->document_number;
                    $MasrafKalem[$key2]["GiderKodu"] = $value2->expense_account;
                    $MasrafKalem[$key2]["KdvMatrahi"] = $value2->price * $value2->quantity;
                    $MasrafKalem[$key2]["KdvOran"] = $value2->kdv;
                    $MasrafKalem[$key2]["Miktar"] = $value2->quantity;
                    $MasrafKalem[$key2]["Tutar"] = $value2->amount;
                    /*if($value2["MSTATUS"]==2)
                        $Silindi++;
                    */
                }
                $CariAciklama = $value->document_number . " NOLU " . $expense->description;
            }
            //if($Silindi>0 && $Silindi==count($MasrafKalem)) continue;

            $MasrafFormu->Aciklama = $CariAciklama;
            $MasrafFormu->BelgeNo = $value->document_number;
            $MasrafFormu->CariKod = $value->netsis_carikod;
            $MasrafFormu->Kalemler = $MasrafKalem;

            $MasrafFormu->Tarih = $value->document_date;
            $MasrafFormu->PersonelCariKodu = $PersonelCariKodu;
            $MasrafFormu->KdvDahil = "E";

            $dbA = date("Y", strtotime($value->document_date));
            try {
                $data = $soap->MasrafFormuKaydet(array("_MasrafForm" => $MasrafFormu, "_IsletmeKodu" => $companyCode, "SirketAdi" => "ASAYGROUP" . $dbA));
            } catch (Exception $e) {
                return response([
                    'status' => false,
                    'data' => $e->getMessage()
                ], 200);
            }

            if ($data->MasrafFormuKaydetResult->Sonuc == 1) {
                $set["netsis"] = 1;
                $set["netsis_document_number"] = "'" . str_replace(" numaral?? masraf dekontu kaydedildi", "", $data->MasrafFormuKaydetResult->Aciklama) . "'";
                ExpenseDocumentModel::where(["id" => $value->id])->update($set);

                $aktar[$value->id]["sonuc"] = true;
                $aktar[$value->id]["aciklama"] = $data->MasrafFormuKaydetResult->Aciklama;
                $aktar[$value->id]["belgeno"] = $value->document_number;
            } else {
                $aktar[$value->id]["sonuc"] = false;
                $aktar[$value->id]["aciklama"] = $data->MasrafFormuKaydetResult->Aciklama;
                $aktar[$value->id]["belgeno"] = $value->document_number;
                $DurumHataSay++;
            }
            $setLog["EXPENSE_ID"] = $expenseId;
            $setLog["DOCUMENT_ID"] = $value->id;
            $setLog["BELGE_NO"] = $value->document_number;
            $setLog["LOG"] = trim($data->MasrafFormuKaydetResult->Aciklama);
            AsayExpenseLogModel::insert($setLog);
        }
        if ($DurumHataSay == 0) {
            ExpenseModel::where(["id" => $expenseId])->update(["status" => 4]);
            $userEmployee = EmployeeModel::find($request->Employee);
            LogsModel::setLog($request->Employee, $expenseId, 1, 7, '', '', $expense->name . ' ba??l??kl?? harcama ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' taraf??ndan NETSIS\'e aktar??ld??.', '', '', '', '', '');
            NotificationsModel::saveNotification($expense->EmployeeID,1,$expense->id,$expense->name,"Harcama talebiniz, muhasebe birimi taraf??ndan onayland??","expense/edit/".$expense->id);
            return response([
                'status' => true,
                'data' => "Masraf Belgeleri Netsise Aktar??ld??"
            ], 200);
        } else {
            return response([
                'status' => false,
                'data' => "Belgelerin Tamam?? Netsise Aktar??lamad??"
            ], 200);
        }
    }

    public function listDocumentTypes(Request $request)
    {
        $documentTypes = ExpenseDocumentTypesModel::where(["active" => 1])->get();

        return response([
            'status' => true,
            'data' => $documentTypes
        ], 200);
    }

    public function getDocumentType(Request $request)
    {
        $documentType = ExpenseDocumentTypesModel::find($request->document_type);

        return response([
            'status' => true,
            'data' => $documentType
        ], 200);
    }

    public function listTypes(Request $request)
    {
        $types = ExpenseTypesModel::where(["active" => 1])->get();

        return response([
            'status' => true,
            'data' => $types
        ], 200);
    }

    public function getType(Request $request)
    {
        $type = ExpenseTypesModel::find($request->expense_type);

        return response([
            'status' => true,
            'data' => $type
        ], 200);
    }

    public function listExpenseAccountCodes(Request $request)
    {
        $responseQ = ExpenseAccountCodesModel::where(["active" => 1]);
        if ($request->document_type !== null)
            $responseQ->where(["document_type" => $request->document_type]);
        if ($request->expense_type !== null)
            $responseQ->where(["expense_type" => $request->expense_type]);
        if ($request->project !== null)
            $responseQ->where(["project" => $request->project]);
        if ($request->project_category !== null)
            $responseQ->where(["project_category" => $request->project_category]);

        $response = $responseQ->get();
        return response([
            'status' => true,
            'data' => $response
        ], 200);
    }

    public function isLoggedPersonIsEmployeeManager(Request $request)
    {
        $expense = ExpenseModel::find($request->expenseId);

        if ($expense->status == 1) {
            $employeePosition = EmployeePositionModel::where(["Active" => 2, "EmployeeID" => $expense->EmployeeID])->first();
            if ($employeePosition->ManagerID == $request->Employee)
                return response([
                    'status' => true,
                    'message' => 'Yetkili Ki??i'
                ], 200);
        }

        return response([
            'status' => false,
            'message' => 'Yetki Yok'
        ], 200);


    }

    public function isManagerApprovedAllDocuments(Request $request)
    {
        $expense = ExpenseModel::find($request->expenseId);
        $expenseOwnersManager = EmployeePositionModel::where('EmployeeID', $expense->EmployeeID)->where('Active', 2)->first();
        if ($request->Employee !== $expenseOwnersManager->ManagerID)
            return response([
                'status' => false,
                'message' => 'Yetkisiz i??lem'
            ], 200);

        $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*", DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
            ->where(["ExpenseDocument.active" => 1, "ExpenseDocument.expense_id" => $expense->id])
            ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
            ->leftJoin("Expense", "ExpenseDocument.expense_id", "=", "Expense.id")
            ->groupBy("ExpenseDocument.id");

        if ($expenseDocumentsQ->count() > 0) {
            $approvedCount = 0;
            $rejectedCount = 0;
            $waitingCount = 0;
            $expenseDocuments = $expenseDocumentsQ->get();
            foreach ($expenseDocuments as $expenseDocument) {
                if ($expenseDocument->manager_status == 1)
                    $approvedCount++;
                elseif ($expenseDocument->manager_status == 2)
                    $rejectedCount++;
                else
                    $waitingCount++;
            }
            if ($waitingCount > 0)
                return response([
                    'status' => false,
                    'message' => 'Onaylanmam???? Belgeler Bulunuyor.'
                ], 200);
            elseif ($approvedCount == 0)
                return response([
                    'status' => false,
                    'message' => 'Onay verilmi?? belge bulunamad??'
                ], 200);
            else
                return response([
                    'status' => true,
                    'message' => 'Belgeler onaylanm????',
                    'type' => 'APPROVED'
                ], 200);
        } else
            return response([
                'status' => false,
                'message' => 'Harcama Belgesi Bulunamad??.'
            ], 200);

    }

    public function isLoggedPersonProjectManager(Request $request)
    {

        $expense = ExpenseModel::find($request->expenseId);
        if ($expense->status == 2) {
            if ($expense->category_id <> "") {
                $projetCategories = ProjectCategoriesModel::find($expense->category_id);
                if ($request->Employee == $projetCategories->manager_id) {
                    return response([
                        'status' => true,
                        'message' => 'Yetkili Ki??i'
                    ], 200);
                }
                return response([
                    'status' => false,
                    'message' => 'Yetki Yok'
                ], 200);

            } else {
                $project = ProjectsModel::find($expense->project_id);
                if ($request->Employee == $project->manager_id) {
                    return response([
                        'status' => true,
                        'message' => 'Yetkili Ki??i'
                    ], 200);
                }
                return response([
                    'status' => false,
                    'message' => 'Yetki Yok'
                ], 200);

            }
        }

        return response([
            'status' => false,
            'message' => 'Yetki Yok'
        ], 200);

    }

    public function isProjectManagerApprovedAllDocuments(Request $request)
    {

        $asayExpense = ExpenseModel::find($request->expenseId);

        if ($asayExpense->status != 2)
            return response([
                'status' => false,
                'message' => 'Harcama Proje Y??netici Onay?? A??amas??nda De??il !'
            ], 200);

        if ($asayExpense->category_id <> "") {
            $projetCategories = ProjectCategoriesModel::find($asayExpense->category_id);
            if ($request->Employee == $projetCategories->manager_id) {

                $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*", DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
                    ->where(["ExpenseDocument.active" => 1, "ExpenseDocument.expense_id" => $asayExpense->id])
                    ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
                    ->leftJoin("Expense", "ExpenseDocument.expense_id", "=", "Expense.id")
                    ->groupBy("ExpenseDocument.id");

                if ($expenseDocumentsQ->count() > 0) {
                    $approvedCount = 0;
                    $rejectedCount = 0;
                    $waitingCount = 0;
                    $expenseDocuments = $expenseDocumentsQ->get();
                    foreach ($expenseDocuments as $expenseDocument) {
                        if ($expenseDocument->pm_status == 1)
                            $approvedCount++;
                        elseif ($expenseDocument->pm_status == 2)
                            $rejectedCount++;
                        else
                            $waitingCount++;
                    }
                    if ($waitingCount > 0)
                        return response([
                            'status' => false,
                            'message' => 'Onaylanmam???? Belgeler Bulunuyor.'
                        ], 200);
                    elseif ($approvedCount == 0)
                        return response([
                            'status' => false,
                            'message' => 'Onay verilmi?? belge bulunamad??'
                        ], 200);
                    else
                        return response([
                            'status' => true,
                            'message' => 'Belgeler onaylanm????',
                            'type' => 'APPROVED'
                        ], 200);
                } else
                    return response([
                        'status' => false,
                        'message' => 'Harcama Belgesi Bulunamad??.'
                    ], 200);


            }

        } else {
            $project = ProjectsModel::find($asayExpense->project_id);
            if ($request->Employee == $project->manager_id) {
                $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*", DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
                    ->where(["ExpenseDocument.active" => 1, "ExpenseDocument.expense_id" => $asayExpense->id])
                    ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
                    ->leftJoin("Expense", "ExpenseDocument.expense_id", "=", "Expense.id")
                    ->groupBy("ExpenseDocument.id");

                if ($expenseDocumentsQ->count() > 0) {
                    $expenseDocuments = $expenseDocumentsQ->get();
                    foreach ($expenseDocuments as $expenseDocument) {
                        if ($expenseDocument->pm_status == 0)
                            return response([
                                'status' => false,
                                'message' => 'Onaylanmam???? Belgeler Bulunuyor.'
                            ], 200);
                    }
                    return response([
                        'status' => true,
                        'message' => 'T??m Belgeler Onaylanm????.'
                    ], 200);
                }
            }

        }

    }

    public function isAccounterApprovedAllDocuments(Request $request)
    {

        $expense = ExpenseModel::find($request->expenseId);

        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID" => $request->Employee, "group_id" => 12, 'active' => 1])->count();
        $processSettingExpenseAccounter = ProcessesSettingsModel::where(['object_type' => 1,'PropertyValue' => $request->Employee,'PropertyCode' => 'Accounter'])->count();
        if ($userGroupCount < 1 && $processSettingExpenseAccounter < 1)
            return response([
                'status' => false,
                'message' => 'Yetkisiz Ki??i.'
            ], 200);

        $expenseDocumentsQ = ExpenseDocumentModel::select("ExpenseDocument.*", DB::raw("SUM(ExpenseDocumentElement.amount) TTUTAR"))
            ->where(["ExpenseDocument.active" => 1, "ExpenseDocument.expense_id" => $expense->id])
            ->leftJoin("ExpenseDocumentElement", "ExpenseDocumentElement.document_id", "=", "ExpenseDocument.id")
            ->leftJoin("Expense", "ExpenseDocument.expense_id", "=", "Expense.id")
            ->groupBy("ExpenseDocument.id");

        if ($expenseDocumentsQ->count() > 0) {
            $expenseDocuments = $expenseDocumentsQ->get();
            if ($expenseDocumentsQ->count() > 0) {
                $approvedCount = 0;
                $rejectedCount = 0;
                $waitingCount = 0;
                $expenseDocuments = $expenseDocumentsQ->get();
                foreach ($expenseDocuments as $expenseDocument) {
                    if ($expenseDocument->accounting_status == 1)
                        $approvedCount++;
                    elseif ($expenseDocument->accounting_status == 2)
                        $rejectedCount++;
                    else
                        $waitingCount++;
                }
                if ($waitingCount > 0)
                    return response([
                        'status' => false,
                        'message' => 'Onaylanmam???? Belgeler Bulunuyor.'
                    ], 200);
                elseif ($approvedCount == 0)
                    return response([
                        'status' => false,
                        'message' => 'Onay verilmi?? belge bulunamad??'
                    ], 200);
                else
                    return response([
                        'status' => true,
                        'message' => 'Belgeler onaylanm????',
                        'type' => 'APPROVED'
                    ], 200);
            } else
                return response([
                    'status' => false,
                    'message' => 'Harcama Belgesi Bulunamad??.'
                ], 200);
        } else
            return response([
                'status' => false,
                'message' => 'Harcama Belgesi Bulunamad??.'
            ], 200);

    }

    public function loggedUsersAuthorizations(Request $request)
    {
        $isEmployeeManager = false;
        $isProjectManager = false;
        $isAccounter = false;

        $employeeManagers = EmployeePositionModel::where(["Active" => 2, "ManagerId" => $request->Employee]);
        $projects = ProjectsModel::where(["manager_id" => $request->Employee]);
        $categories = ProjectCategoriesModel::where(["manager_id" => $request->Employee]);

        if ($projects->count() > 0)
            $isProjectManager = true;
        if ($categories->count() > 0)
            $isProjectManager = true;
        if ($employeeManagers->count() > 0)
            $isEmployeeManager = true;

        $userGroupCount = EmployeeHasGroupModel::where(["EmployeeID" => $request->Employee, "group_id" => 12])->count();
        if ($userGroupCount > 0)
            $isAccounter = true;

        $data['isEmployeeManager'] = $isEmployeeManager;
        $data['isProjectManager'] = $isProjectManager;
        $data['isAccounter'] = $isAccounter;

        return response([
            'status' => true,
            'message' => '????lem Ba??ar??l??',
            'data' => $data
        ], 200);
    }

    public function printExpense(Request $request)
    {
        $expenseId = $request->expenseId;

        if (is_null($expenseId) || !isset($expenseId))
            return response([
                'status' => false,
                'message' => 'Harcama Id Bo?? Olamaz!'
            ], 200);

        $expense = ExpenseModel::find($expenseId);

        if ($expense->status < 3)
            return response([
                'status' => false,
                'message' => 'Harcama Y??netici Onay??ndan Ge??memi??!'
            ], 200);

        $expenseDocuments = ExpenseDocumentModel::where(['active' => 1, 'expense_id' => $expenseId])->
        where('manager_status', '<>', '2')->
        where('pm_status', '<>', '2')->
        where('accounting_status', '<>', '2')->get();

        $expenseDocumentElements = [];
        $expenseDocumentElementsTotalPrice = 0;
        foreach ($expenseDocuments as $key => $expenseDocument) {
            $documentElements = ExpenseDocumentElementModel::where(['active' => 1, 'document_id' => $expenseDocument->id])->get();
            foreach ($documentElements as $documentElement) {
                $expenseDocumentElementsTotalPrice += $documentElement->price;
                array_push($expenseDocumentElements, $documentElement);
            }
        }

        return response([
            'status' => true,
            'message' => '????lem Ba??ar??l??',
            'data' => [
                'expense' => $expense,
                'expenseDocuments' => $expenseDocuments,
                'documentElements' => $expenseDocumentElements,
                'elementsTotalPrice' => $expenseDocumentElementsTotalPrice
            ]
        ], 200);

    }

    public function restartExpenseProcess(Request $request)
    {

        $expenseID = $request->expenseID;

        $expense = ExpenseModel::find($expenseID);

        $expenseDocuments = ExpenseDocumentModel::where(['expense_id' => $expense->id])->get();

        foreach ($expenseDocuments as $expenseDocument)
        {
            $expenseDocument->netsis = 0;
            $expenseDocument->accounting_status = 0;
            $expenseDocument->pm_status = 0;
            $expenseDocument->manager_status = 0;
            $expenseDocument->save();
        }

        $expense->reject_status = 0;
        $expense->status = 1;
        $result = $expense->save();

        if ($result)
            return response([
                'status' => true,
                'message' => '????lem Ba??ar??l??',
            ],200);
        else
            return response([
                'status' => false,
                'message' => '????lem Ba??ar??s??z',
            ],200);


    }

    /*
    public function test()
    {
        //SMM Sorgulama
        exit;
        $wsdl    = 'http://smmmservis.tnb.org.tr/NPSKimlikDogrulamaServisi/Service.asmx?wsdl';

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
            "location" => "http://smmmservis.tnb.org.tr/NPSKimlikDogrulamaServisi/Service.asmx??singleWsdl",
        );
        try
        {
            $disKullaniciKimlik = new \stdClass();
            $disKullaniciKimlik->ID = 1;
            $disKullaniciKimlik->KayitDurumu = "Added";
            $disKullaniciKimlik->ProgramAdi = "Belirtilmemis";
            $disKullaniciKimlik->KimlikNO = "2650132174";
            $disKullaniciKimlik->KimlikNOTipi = "VKN";
            $disKullaniciKimlik->NoterlikKodu = "";
            $disKullaniciKimlik->NoterlikKullaniciAdi = "";
            $disKullaniciKimlik->DisKullaniciTipi = "Belirtilmemis";
            $disKullaniciKimlik->Sifre = "";
            $soap = new SoapClient($wsdl, $options);
            $data = $soap->DisKullaniciKimlikDogrula([
                "disKullaniciKimlik" => $disKullaniciKimlik,
                "islemTipi" => "Belirtilmemis",
                "istemciTarihi" => date("Y-m-d H:i:s"),
            ]);
            dd($data);
            return response([
                'status' => false,
                'message' => $data
            ], 200);

        }
        catch(Exception $e)
        {
            return response([
                'status' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }*/

}
