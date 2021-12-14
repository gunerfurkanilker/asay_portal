<?php

namespace App\Http\Controllers\Api\Processes;

use App\Model\IdCardModel;
use App\Model\UserTokensModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;

use Illuminate\Support\Facades\Auth;

use App\Model\PerformanceModel;

use App\Http\Resources\PerformanceResource;
use Carbon\Carbon;
use App\Model\PerformanceWeightModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PerformanceController extends ApiController
{
    //

    public function toExcel(Request $request){


        $list = [];
       // $authuser=UserTokensModel::where('user_token',$request->token)->first()->user;
        $user=UserTokensModel::where('user_token',$request->token)->first()->user;

        $spreadsheet = new Spreadsheet();

        $workSheet = new Worksheet($spreadsheet, 'Performance');

        $columns = [
            'Yöneticisi',
            'Çalışan',
            'Departmanı',
            'Ünvanı',
            'Çalıştığı Bölge',
            'Çalıştığı İl',
            'Birim Sorumlusu',
            'Değerlendirme Dönemi',
            'Durumu',
            'Teknik Bilgi ve Beceri',
            'Zaman Yönetimi',
            'Ekip Çalışması',
            'Teknolojiye Hakimiyet',
            'Sorumluluk',
            'İletişim Becerileri',
            'Müşteri Odaklılık',
            'Güvenli İş Ortamı Sağlamak',
            'İşten Ayrılma Durumu Etkiler Mi?',
            'Açıklama'

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
        $list=$user->employees;
        foreach ($list as $keyItem => $item)
        {


            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $asciiCapitalA = 65;
            $values = [];

            $employee = EmployeeModel::find($item->EmployeeID);
            $employeePosition = EmployeePositionModel::where('EmployeeID',$item->EmployeeID)->first();

            $employeeManager = $user->FirstName .' '. $user->LastName;
            $employeeFullName = $employee->FirstName ?? '';
            $employeeLastName = $employee->LastName ?? '';
            $employeeName = $employeeFullName . " ".$employeeLastName;

            $employeeDepartment = $employeePosition->Department->Sym;


            $employeeTitle = $employeePosition->Title->Sym;
            $employeeRegion = $employeePosition->Region->Sym;
            $employeeCity = $employeePosition->City->Sym;
            $employeeUnitSupervisor = EmployeeModel::find($employeePosition->UnitSupervisorID)->full_name ?? '';
                $performance = "test";
                $employeeStatus = $employee->performance_status ?? 'Bekliyor';



                $performanceWeight = PerformanceWeightModel::where('EmployeeID',$item->EmployeeID)->first();

            $employeeEvualationPeriod =  isset($performanceWeight->created_at) ? (Carbon::parse($performanceWeight->created_at )->year.' Yılı') : Carbon::now()->year. 'Yılı';
                $employeeTech = $performanceWeight->TechKnowledge ?? '';
                $employeeTime = $performanceWeight->TimeManagement ?? '';
                $employeeTeam = $performanceWeight->Teamwork ?? '';
                $employeeMastery = $performanceWeight->MasteryOfTech ?? '';
                $employeeResponsibility = $performanceWeight->Responsibility ?? '';
                $employeeCom = $performanceWeight->CommunicationSkills ?? '';
                $employeeCustomer = $performanceWeight->CustomerFocus ?? '';
                $employeeSafe = $performanceWeight->SafeWorkProvider ?? '';
                $employeeExit = $performanceWeight->ExitEffect ?? '';
                $employeeExitReason = $performanceWeight->ExitEffectReason ?? '';



            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values,$employeeManager);
            array_push($values,$employeeName);
            array_push($values,$employeeDepartment);
            array_push($values,$employeeTitle);
            array_push($values,$employeeRegion);
            array_push($values,$employeeCity);
            array_push($values,$employeeUnitSupervisor);
            array_push($values,$employeeEvualationPeriod);
            array_push($values,$employeeStatus);
            array_push($values,$employeeTech>=0 ? $this->performanceWeightToText($employeeTech) : '');
            array_push($values,$employeeTime>=0 ? $this->performanceWeightToText($employeeTime) : '');
            array_push($values,$employeeTeam>=0 ? $this->performanceWeightToText($employeeTeam) : '');
            array_push($values,$employeeMastery>=0 ? $this->performanceWeightToText($employeeMastery) : '');
            array_push($values,$employeeResponsibility>=0 ? $this->performanceWeightToText($employeeResponsibility) : '');
            array_push($values,$employeeCom>=0 ? $this->performanceWeightToText($employeeCom) : '');
            array_push($values,$employeeCustomer>=0 ? $this->performanceWeightToText($employeeCustomer) : '');
            array_push($values,$employeeSafe>=0 ? $this->performanceWeightToText($employeeSafe) : '');
            array_push($values,$employeeExit>=0 ? $this->performanceReasonToText($employeeExit) : '');
            array_push($values,$employeeExitReason);



            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($keyItem+2),$values[$keyColumns]);
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

        Storage::disk('')->put("Performance.xlsx", $content);
        return response()->download(storage_path('app/' . "Performance.xlsx"));


    }

    public function performanceWeightToText($weight)
    {
        if($weight===100)
            return 'Çok İyi';
        if($weight===75)
            return 'İyi';
        if($weight===50)
            return 'Orta';
        if($weight===25)
            return 'Kötü';
        if($weight===0)
            return 'Çok Kötü';
    }

    public function performanceReasonToText($weight)
    {
        if($weight===0)
            return 'Az';
        if($weight===1)
            return 'Orta';
        if($weight===2)
            return 'Çok';
    }



    private $permission = array();

    public function getManagersEmployees(Request $request)
    {
        $paginationPage = ($request->PaginationPage - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;
        $year = $request->Year;
        $month = $request->Month;
        $employee = $request->AssignedID;
        $managerId = $request->Employee;
        //  dd($request->TitleID);


        $userEmployees = PerformanceResource::collection(EmployeePositionModel::Where(['ManagerID' => $managerId])->get());

        return response()->json($userEmployees);
    }

    public function test1(Request $request)
    {
        $request->validate([
            "EmployeeID" => 'required',
            "TechKnowledge" => 'required',
            "TimeManagement" => 'required',
            "Teamwork" => 'required',
            "MasteryOfTech" => 'required',
            "Responsibility" => 'required',
            "CommunicationSkills" => 'required',
            "CustomerFocus" => 'required',
            "SafeWorkProvider" => 'required',
            "ExitEffect" => 'required',
            "ExitEffectReason"=>'required'
            ]);

            $data = $request->except('token');

            PerformanceWeightModel::updateOrCreate([
                'EmployeeID' => $request->EmployeeID,

            ], $request->except(['EmployeeID', 'token']));
            return response()->json([
                'success' => true,
                'message' => 'Başarıyla Eklendi'
            ]);
        }


}
