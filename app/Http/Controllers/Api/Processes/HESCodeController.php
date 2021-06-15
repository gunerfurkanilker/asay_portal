<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\HESCodeModel;
use App\Model\IdCardModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HESCodeController extends ApiController
{

    public function saveHesCode(Request $request){

        $hesCodeID = $request->HesCodeID;

        $hesCode = null;
        if (is_null($hesCodeID) || $hesCodeID == "")
            $hesCode = new HESCodeModel();
        else
        {
            $hesCode = HESCodeModel::where(['Active' => 1, 'id' => $hesCodeID])->first();
            if (!$hesCode)
                return response([
                    'status' => false,
                    'message' => 'Kayıt bulunamadı'
                ],200);
        }

        $hesCode->EmployeeID = $request->Employee;
        $hesCode->Code = $request->Code;
        $hesCode->HesCodeType = $request->HesCodeType;
        $hesCode->RemainingDay = $request->HesCodeType == 1 ? $request->RemainingDay : null;
        $hesCode->ExpireDate = $request->HesCodeType == 1 ? date('Y.m.d',strtotime("+". $request->RemainingDay ."days")) : null;

        try{
            if ($hesCode->save())
                return response([
                    'status' => true,
                    'message' => 'Kayıt Başarılı'
                ],200);
            else
                return response([
                    'status' => false,
                    'message' => 'Kayıt Başarısız'
                ],200);
        }catch (QueryException $e){
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return response([
                    'status' => false,
                    'message' => 'Girmiş olduğunuz HES Kodu ile daha önce bir kayıt oluşturulmuştur'
                ],200);
            }
        }




    }

    public function deleteHesCode(Request $request){
        $hesCodeID = $request->HesCodeID;

        $hesCode = HESCodeModel::find($hesCodeID);

        if (!$hesCode)
            return response([
                'status' => false,
                'message' => 'Kayıt Bulunamadı, İşlem Başarısız'
            ],200);

        if ($hesCode->delete()){
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ],200);
        }
        return response([
            'status' => false,
            'message' => 'İşlem Başarısız'
        ],200);


    }

    public function listHesCodes(Request $request){

        $allData = $request->All;

        $hesCodesLimitless = HESCodeModel::where(['Active' => 1, 'HesCodeType' => 2]); // Süresiz olanlar
        $hesCodesLimit = HESCodeModel::where(['Active' => 1, 'HesCodeType' => 1]);


        if (!$allData || !isset($allData)){
            $hesCodesLimit->where("EmployeeID",$request->Employee);
            $hesCodesLimitless->where("EmployeeID",$request->Employee);
        }

        $hesCodesLimitless->orderBy("ExpireDate","desc");
        $hesCodesLimit->orderBy("ExpireDate","desc");


        $hesCodesLimitless = $hesCodesLimitless->get();
        $hesCodesLimit = $hesCodesLimit->get();


        $hesCodes = [];
        foreach ($hesCodesLimitless as $item) {
            array_push($hesCodes,$item);
        }
        foreach ($hesCodesLimit as $item) {
            array_push($hesCodes,$item);
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $hesCodes
        ],200);

    }

    public function getHesCode(){

    }

    public function toExcel(){

        $hesCodes = [];

        $hesCodesLimitless = HESCodeModel::where(["HesCodeType" => 2, 'Active' => 1])->get();
        $hesCodesLimit = HESCodeModel::where(["HesCodeType" => 1, 'Active' => 1])->orderBy("ExpireDate","desc")->get();

        foreach ($hesCodesLimitless as $item)
            array_push($hesCodes,$item);
        foreach ($hesCodesLimit as $item)
            array_push($hesCodes,$item);


        $spreadsheet = new Spreadsheet();

        $workSheet = new Worksheet($spreadsheet, 'HES Kodları');

        $columns = [
            'T.C Kimlik No',
            'HES Kodu',
            'HES Kodu Kalan Süre (Gün)',
            'HES Kodu Bitiş Tarihi',
            'Personel ID',
            'Tam Adı',
            'Kullandığı Adı',
            'Soyadı',

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



        foreach ($hesCodes as $keyHesCode => $hesCode)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employee = EmployeeModel::find($hesCode->EmployeeID);
            $employeeTCKN = IdCardModel::where("Id",$employee->EmployeeID)->first();
            $employeeTCKN = $employeeTCKN ? $employeeTCKN->TCNo : $employee->UsageName . ' ' . $employee->LastName;
            $employeeStaffID = $employee->StaffID;
            $employeeFullName = $employee->FirstName;
            $employeeUsageName = $employee->UsageName;
            $employeeLastName = $employee->LastName;
            $hesCodeValue = $hesCode->Code;
            $hesCodeRemainingDay = $hesCode->RemainingDay ? $hesCode->RemainingDay : 'Süresiz' ;
            $hesCodeExpireDate = $hesCode->ExpireDate ? date("d.m.Y",strtotime($hesCode->ExpireDate)) : 'Süresiz';

            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeTCKN);
            array_push($values,$hesCodeValue);
            array_push($values,$hesCodeRemainingDay);
            array_push($values,$hesCodeExpireDate);
            array_push($values,$employeeStaffID);
            array_push($values,$employeeFullName);
            array_push($values,$employeeUsageName);
            array_push($values,$employeeLastName);



            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyHesCode+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $spreadsheet->addSheet($workSheet,0);


        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("HESCodes.xlsx", $content);
        return response()->download(storage_path('app/' . "HESCodes.xlsx"));

    }

}
