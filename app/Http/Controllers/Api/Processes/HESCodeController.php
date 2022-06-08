<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Library\Asay;
use App\Model\EmployeeModel;
use App\Model\HESCodeModel;
use App\Model\IdCardModel;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class HESCodeController extends ApiController
{

    public function saveHesCode(Request $request)
    {

        $hesCodeID = $request->HesCodeID;

        $hesCode = null;
        if (is_null($hesCodeID) || $hesCodeID == "")
            $hesCode = new HESCodeModel();
        else {
            $hesCode = HESCodeModel::where(['Active' => 1, 'id' => $hesCodeID])->first();
            if (!$hesCode)
                return response([
                    'status' => false,
                    'message' => 'Kayıt bulunamadı'
                ], 200);
        }

        $hesCode->EmployeeID = $request->Employee;
        $hesCode->Code = $request->Code;
        $hesCode->HesCodeType = $request->HesCodeType;
        $hesCode->RemainingDay = $request->HesCodeType == 1 ? $request->RemainingDay : null;
        $hesCode->ExpireDate = $request->HesCodeType == 1 ? date('Y.m.d', strtotime("+" . $request->RemainingDay . "days")) : null;

        try {
            if ($hesCode->save()){
                $this->checkHesCode($hesCode);
                return response([
                    'status' => true,
                    'message' => 'Kayıt Başarılı'
                ], 200);
            }
            else
                return response([
                    'status' => false,
                    'message' => 'Kayıt Başarısız'
                ], 200);
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return response([
                    'status' => false,
                    'message' => 'Girmiş olduğunuz HES Kodu ile daha önce bir kayıt oluşturulmuştur'
                ], 200);
            }
        }


    }

    public function deleteHesCode(Request $request)
    {
        $hesCodeID = $request->HesCodeID;

        $hesCode = HESCodeModel::find($hesCodeID);

        if (!$hesCode)
            return response([
                'status' => false,
                'message' => 'Kayıt Bulunamadı, İşlem Başarısız'
            ], 200);

        if ($hesCode->delete()) {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ], 200);
        }
        return response([
            'status' => false,
            'message' => 'İşlem Başarısız'
        ], 200);


    }


    public function jsonParser($str)
    {
        $str = str_replace(['{', '}', '"'], '', $str);
        $exploded = explode(',', $str);
        $parser = [];
        $i = 0;
        while ($i < 9) {
//            array_push($parser,[
//                explode(':',$exploded[$i])[0]=>explode(':',$exploded[$i])[1]
//            ]);
            $parser[explode(':', $exploded[$i])[0]] = explode(':', $exploded[$i])[1];
            $i++;
        }
        return $parser;
    }

    public function checkHesCode($item)
    {
        //$item->employee->employeeposition->Manager->MobilePhone;

        try {
            //   if($item->employee->employeeposition->Manager->MobilePhone)


            $hesCode = $item->Code;
            $hesCode = str_replace("-", "", $hesCode);
            $client = new \GuzzleHttp\Client();
            $sendData = [
                'json' => [
                    "hes_code" => $hesCode
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic QVNBWS1HMkctSEVTOkFqMnhHblJ0NVdXNkhzVFFBRA=='
                ]
            ];
            $url = 'https://hesservis.turkiye.gov.tr/services/g2g/saglik/hes/check-hes-code-plus';
            $res = $client->request("POST", $url, $sendData);
            $posts = $res->getBody();
            $hesResult = $this->jsonParser($posts);
            // return $hesResult;
            //    {"expiration_date":"2070-12-30T13","current_health_status":"RISKLESS","masked_identity_number":"********792","masked_firstname":"DE****","masked_lastname":"DE**** ","is_vaccinated":"true","is_immune":"false","is_test_data_shared":"true","last_negative_test_date":"null"}

            $hesModel = HESCodeModel::findOrFail($item->id);
            $hesModel->update([
                'vaccineStatus' => $hesResult['is_vaccinated'] == "true" ? 'Aşılı' : 'Aşısız',
                'pastDisease' => $hesResult['is_immune'] == "true" ? 'Var' : 'Yok',
                'pcrTest' => $hesResult['last_negative_test_date'] == "null" ? 'Yok' : 'Var',
                'status' => $hesResult['current_health_status'] == "RISKLESS" ? 'Risksiz' : 'Riskli',
                'requestDate' => Carbon::now()
            ]);
            if ($hesResult['current_health_status'] != "RISKLESS")
            {
                $msg=$item->requestDate . ' tarihinde COVID önlemleri gereği yapılan HES Kodu sorgulamasında aşağıda bilgileri yer alan çalışanda riskli durum tespit edilmiştir, Toplum Sağlığı ve İş Sağlığı ve Güvenliği nedeni ile en kısa sürede ilgili çalışan ile iletişime geçilerek gerekli süreçlerin işletilmesi gerekmektedir.

                \n Çalışan Adı Soyadı:' . $item->employee->FirstName . ' ' . $item->employee->LastName . '
\n Çalışan GSM No:' . ($item->employee->JobMobilePhone ?? '') . '
\n Çalışan Departman:' . ($item->employee->employeeposition->Department->Sym ?? '') . '
\n Çalıştığı İl: ' . ($item->employee->employeeposition->City->Sym ?? '') . '
\n Çalışanın Yöneticisi:' . ($item->employee->employeeposition->Manager->FirstName ?? '') . ' ' . ($item->employee->employeeposition->Manager->LastName ?? '');

                if(!$hesModel->positiveStatus){
                    $hesModel->update([
                       'positiveStatus'=>1,
                       'positiveDate'=>Carbon::now()
                    ]);
                    Asay::sendSMS($msg
                        ,$item->employee->employeeposition->Manager->JobMobilePhone);

                    //
                }
            }
        } catch (\Exception $e) {

        }

        //return response(gettype($hesResult['is_vaccinated']));


//
//
//        $hesCode=$item->Code;
//        $link = 'https://hesservis.turkiye.gov.tr/services/g2g/saglik/hes/check-hes-code-plus';
//        $client = new Client([
//            'auth' => ['ASAY-G2G-HES', 'Aj2xGnRt5WW6HsTQAD']
//        ]);
//        $hesCode=str_replace("-","",$hesCode);
//        $response = $client->request("post",$link, [
//            'debug' => TRUE,
//            'body'=>'{
//                    "hes_code":"'.$hesCode.'"
//                    }',
//            'headers' => [
//                'Content-Type' => 'application/json',
//                'Accept' => 'application/json',
//                'Authorization'=>'Basic QVNBWS1HMkctSEVTOkFqMnhHblJ0NVdXNkhzVFFBRA=='
//            ]
//        ]);
//        $responseHes=$response->getBody();
//        return response($responseHes);
    }

    public function listHesCodes(Request $request)
    {

        $allData = $request->All;

        $hesCodesLimitless = HESCodeModel::where(['Active' => 1, 'HesCodeType' => 2]); // Süresiz olanlar
        $hesCodesLimit = HESCodeModel::where(['Active' => 1, 'HesCodeType' => 1]);


        if (!$allData || !isset($allData)) {
            $hesCodesLimit->where("EmployeeID", $request->Employee);
            $hesCodesLimitless->where("EmployeeID", $request->Employee);
        }

        $hesCodesLimitless->orderBy("ExpireDate", "desc");
        $hesCodesLimit->orderBy("ExpireDate", "desc");


        $hesCodesLimitless = $hesCodesLimitless->get();
        $hesCodesLimit = $hesCodesLimit->get();


        $hesCodes = [];
        foreach ($hesCodesLimitless as $item) {
           // $this->checkHesCode($item);

            array_push($hesCodes, $item);
        }
        foreach ($hesCodesLimit as $item) {
          //  $this->checkHesCode($item);

            array_push($hesCodes, $item);
        }


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'test' => $_SERVER['SERVER_ADDR'],
            'data' => $hesCodes
        ], 200);

    }

    public function getHesCode()
    {

    }

    public function toExcel()
    {

        $hesCodes = [];

        $hesCodesLimitless = HESCodeModel::where(["HesCodeType" => 2, 'Active' => 1])->get();
        $hesCodesLimit = HESCodeModel::where(["HesCodeType" => 1, 'Active' => 1])->orderBy("ExpireDate", "desc")->get();

        foreach ($hesCodesLimitless as $item)
            array_push($hesCodes, $item);
        foreach ($hesCodesLimit as $item)
            array_push($hesCodes, $item);


        $spreadsheet = new Spreadsheet();

        $workSheet = new Worksheet($spreadsheet, 'HES Kodları');

        $columns = [
            'Personel ID',
            'Adı Soyadı',
            'Departman',
            'Unvan',
            'Yöneticisi',
            'Çalıştığı İl',
            'HES Kodu',
            'Geçerlilik Tarihi',
            'Aşı Durumu',
            'Geçirilmiş Hastalık',
            'PCR Test',
            'Durumu',
            'Sorgu Tarihi'

        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column) {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter . "1", $column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(40);
            $asciiCapitalA++;
        }


        foreach ($hesCodes as $keyHesCode => $hesCode) {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];
            $employee = EmployeeModel::find($hesCode->EmployeeID);
            $employeeStaffID = $employee->StaffID;
            $employeeFullName = $employee->FirstName . ' ' . $employee->LastName;
            $departmant = $employee->EmployeePosition ? $employee->EmployeePosition->Department ? $employee->EmployeePosition->Department->Sym : '' : '';
            $title = $employee->EmployeePosition ? $employee->EmployeePosition->Title ? $employee->EmployeePosition->Title->Sym : '' : '';
            $employeeManager = $employee->EmployeePosition ? $employee->EmployeePosition->Manager ? $employee->EmployeePosition->Manager->FirstName . ' ' . $employee->EmployeePosition->Manager->LastName : '' : '';
            $employeeCity = $employee->EmployeePosition ? $employee->EmployeePosition->City ? $employee->EmployeePosition->City->Sym : '' : '';
            $hesCodeValue = $hesCode->Code;
            $hesCodeExpireDate = $hesCode->ExpireDate ? date("d.m.Y", strtotime($hesCode->ExpireDate)) : 'Süresiz';
            $vaccineStatus = $hesCode->vaccineStatus;
            $pastDisease = $hesCode->pastDisease;
            $pcr = $hesCode->pcrTest;
            $status = $hesCode->status;
            $requestDate = $hesCode->requestDate;


            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values, $employeeStaffID);
            array_push($values, $employeeFullName);
            array_push($values, $departmant);
            array_push($values, $title);
            array_push($values, $employeeManager);
            array_push($values, $employeeCity);
            array_push($values, $hesCodeValue);
            array_push($values, $hesCodeExpireDate);
            array_push($values, $vaccineStatus);
            array_push($values, $pastDisease);
            array_push($values, $pcr);
            array_push($values, $status);
            array_push($values, $requestDate);


            foreach ($columns as $keyColumns => $column) {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter . ($keyHesCode + 2), $values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $spreadsheet->addSheet($workSheet, 0);


        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("HESCodes.xlsx", $content);
        return response()->download(storage_path('app/' . "HESCodes.xlsx"));

    }

    public function toCheckHesCodeMail(Request $request)
    {
        $employeeID = $request->EmployeeID;
        $mail = $request->JobMail;
        $hesCodesLimitless = HESCodeModel::where(["HesCodeType" => 2, 'Active' => 1])->get();
        $hesCodesLimit = HESCodeModel::where(["HesCodeType" => 1, 'Active' => 1])->orderBy("ExpireDate", "desc")->get();
        $counter1 = 0;
        $counter2 = 0;
        foreach ($hesCodesLimitless as $hes) {
            if ($hes->EmployeeID == $employeeID) {
                $counter1++;
            }
        }
        foreach ($hesCodesLimit as $hesLimit) {
            if ($hes->EmployeeID == $employeeID) {
                $counter2++;
            }
        }
        if ($counter1 == 0 && $counter2 == 0) {
            $mailData = [
                'mailContext' => "Eğitim geçerlilik tarihi süresininin bitimine 15 günden az kalmış kayıtlar aşağıdaki gibidir"
            ];
            $mailTable = view('mails.isg-expire-trainings', $mailData);
            Asay::sendMail("{$mail}", "", "Geçerlilik süresinin dolmasına 15 gün kalmış eğitimler", "$mailTable", "aSAY Group", "", "", "");
        }
    }

    public function  checkEmployeeTC(Request $request){

    }

}
