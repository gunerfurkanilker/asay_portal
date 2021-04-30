<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Library\Asay;
use App\Model\ContractTypeModel;
use App\Model\EducationLevelModel;
use App\Model\Employee;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeLogsModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\EmployeePropertyValuesModel;
use App\Model\EmployeesChildModel;
use App\Model\GenderModel;
use App\Model\HealthReportModel;
use App\Model\IdCardModel;
use App\Model\PaymentModel;
use App\Model\PermitModel;
use App\Model\ProcessesSettingsModel;
use App\Model\RelationshipDegreeModel;
use App\Model\UserGroupModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class EmployeeController extends ApiController
{

    public function dailyEmployeeStatusReportExcel(Request $request){

        $healthReportEmployeeIDs = HealthReportModel::where(['Active' => 1])
            ->whereDate('start_date', '<=', date("Y-m-d"))
            ->whereDate('end_date', '>=', date("Y-m-d"))
            ->pluck("EmployeeID")
            ->toArray();
        $havePermitEmployeeIDs = PermitModel::where(['active' => 1])
            ->whereDate('start_date', '<=', date("Y-m-d"))
            ->whereDate('end_date', '>=', date("Y-m-d"))
            ->where("status",">=",3)
            ->where("manager_status", "!=", 2)
            ->where("hr_status", "!=", 2)
            ->where("ps_status", "!=", 2)
            ->pluck("EmployeeID")
            ->toArray();

        //Test Userları listeden çıkarılacak.
        $healthReportEmployees = EmployeeModel::where("Active",1)->where("Id",">","999")->whereIn("Id", $healthReportEmployeeIDs)->get();
        $havePermitEmployees = EmployeeModel::where("Active",1)->where("Id",">","999")->whereIn("Id", $havePermitEmployeeIDs)->get();
        $activeEmployees = EmployeeModel::where("Active",1)->where("Id",">","999")->whereNotIn("Id", array_merge($healthReportEmployeeIDs,$havePermitEmployeeIDs))->get();

        $spreadsheet = new Spreadsheet();
        $workSheet = new Worksheet($spreadsheet, 'Rapor');

        $columns = [
            'Departman',
            'Hizmet Kodu',
            'Bölge',
            'Çalıştığı İl',
            'Çalışma Alanı',
            'Personel ID',
            'Tam Adı',
            'Soyadı',
            'Unvan',
            'Yöneticisi',
            'Durumu',
            'Check-In'
        ];

        //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
        $asciiCapitalA = 65;
        foreach ($columns as $key => $column)
        {
            $columnLetter = chr($asciiCapitalA);
            $workSheet->setCellValue($columnLetter."4",$column);
            $workSheet->getColumnDimension($columnLetter)->setAutoSize(false)->setWidth(30);
            $asciiCapitalA++;
        }



        $rowNum = 5;
        while (true) {
            foreach ($healthReportEmployees as $keyEmployee => $employee) {
                //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
                $asciiCapitalA = 65;
                $values = [];
                $employeeDepartment = $employee->EmployeePosition ? $employee->EmployeePosition->Department ?  $employee->EmployeePosition->Department->Sym : '' :'';
                $employeeServiceCode = $employee->EmployeePosition ? $employee->EmployeePosition->ServiceCode  :'';
                $employeeRegion = $employee->EmployeePosition ? $employee->EmployeePosition->Region ? $employee->EmployeePosition->Region->Name : '' :'';
                $employeeCity = $employee->EmployeePosition ? $employee->EmployeePosition->City ? $employee->EmployeePosition->City->Sym : '' :'';
                $employeeWorkingField = $employee->EmployeePosition ? $employee->EmployeePosition->WorkingField ? $employee->EmployeePosition->WorkingField->Name : '' :'';
                $employeeStaffID = $employee->StaffID;
                $employeeFullName = $employee->FirstName ;
                $employeeLastName = $employee->LastName;
                $employeeTitle = $employee->EmployeePosition ? $employee->EmployeePosition->Title ? $employee->EmployeePosition->Title->Sym : '' :'';
                $employeeManager = $employee->EmployeePosition ? $employee->EmployeePosition->Manager ? $employee->EmployeePosition->Manager->FirstName . ' ' . $employee->EmployeePosition->Manager->LastName  : '' :'';
                $healthReportTypes = HealthReportModel::where(['Active' => 1])
                    ->whereDate('start_date', '<=', date("Y-m-d"))
                    ->whereDate('end_date', '>=', date("Y-m-d"))
                    ->get();

                $healthReportTypeString = "";
                foreach ($healthReportTypes as $healthReportType)
                {
                    $healthReportTypeString = $healthReportTypeString . " " . $healthReportType->DocumentType->Name;
                }


                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values, $employeeDepartment);
                array_push($values, $employeeServiceCode);
                array_push($values, $employeeRegion);
                array_push($values, $employeeCity);
                array_push($values, $employeeWorkingField);
                array_push($values, $employeeStaffID);
                array_push($values, $employeeFullName);
                array_push($values, $employeeLastName);
                array_push($values, $employeeTitle);
                array_push($values, $employeeManager);
                array_push($values, $healthReportTypeString);
                array_push($values, "");//Check-In


                foreach ($columns as $keyColumns => $column) {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter . ($rowNum), $values[$keyColumns]);
                    $asciiCapitalA++;
                }
                $rowNum++;
            }

            foreach ($havePermitEmployees as $keyEmployee => $employee) {
                //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
                $asciiCapitalA = 65;
                $values = [];
                $employeeDepartment = $employee->EmployeePosition ? $employee->EmployeePosition->Department ?  $employee->EmployeePosition->Department->Sym : '' :'';
                $employeeServiceCode = $employee->EmployeePosition ? $employee->EmployeePosition->ServiceCode  :'';
                $employeeRegion = $employee->EmployeePosition ? $employee->EmployeePosition->Region ? $employee->EmployeePosition->Region->Name : '' :'';
                $employeeCity = $employee->EmployeePosition ? $employee->EmployeePosition->City ? $employee->EmployeePosition->City->Sym : '' :'';
                $employeeWorkingField = $employee->EmployeePosition ? $employee->EmployeePosition->WorkingField ? $employee->EmployeePosition->WorkingField->Name : '' :'';
                $employeeStaffID = $employee->StaffID;
                $employeeFullName = $employee->FirstName ;
                $employeeLastName = $employee->LastName;
                $employeeTitle = $employee->EmployeePosition ? $employee->EmployeePosition->Title ? $employee->EmployeePosition->Title->Sym : '' :'';
                $employeeManager = $employee->EmployeePosition ? $employee->EmployeePosition->Manager ? $employee->EmployeePosition->Manager->FirstName . ' ' . $employee->EmployeePosition->Manager->LastName  : '' :'';

                $permit = PermitModel::where(['EmployeeID' => $employee->Id, 'active' => 1])
                    ->whereDate('start_date', '<=', date("Y-m-d"))
                    ->whereDate('end_date', '>=', date("Y-m-d"))
                    ->where("status",">=",3)
                    ->where("manager_status", "!=", 2)
                    ->where("hr_status", "!=", 2)
                    ->where("ps_status", "!=", 2)
                    ->first();

                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values, $employeeDepartment);
                array_push($values, $employeeServiceCode);
                array_push($values, $employeeRegion);
                array_push($values, $employeeCity);
                array_push($values, $employeeWorkingField);
                array_push($values, $employeeStaffID);
                array_push($values, $employeeFullName);
                array_push($values, $employeeLastName);
                array_push($values, $employeeTitle);
                array_push($values, $employeeManager);
                array_push($values, $permit->PermitKind['name']);
                array_push($values, "");//Check-In


                foreach ($columns as $keyColumns => $column) {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter . ($rowNum), $values[$keyColumns]);
                    $asciiCapitalA++;
                }
                $rowNum++;
            }

            foreach ($activeEmployees as $keyEmployee => $employee) {
                //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
                $asciiCapitalA = 65;
                $values = [];
                $employeeDepartment = $employee->EmployeePosition ? $employee->EmployeePosition->Department ?  $employee->EmployeePosition->Department->Sym : '' :'';
                $employeeServiceCode = $employee->EmployeePosition ? $employee->EmployeePosition->ServiceCode  :'';
                $employeeRegion = $employee->EmployeePosition ? $employee->EmployeePosition->Region ? $employee->EmployeePosition->Region->Name : '' :'';
                $employeeCity = $employee->EmployeePosition ? $employee->EmployeePosition->City ? $employee->EmployeePosition->City->Sym : '' :'';
                $employeeWorkingField = $employee->EmployeePosition ? $employee->EmployeePosition->WorkingField ? $employee->EmployeePosition->WorkingField->Name : '' :'';
                $employeeStaffID = $employee->StaffID;
                $employeeFullName = $employee->FirstName ;
                $employeeLastName = $employee->LastName;
                $employeeTitle = $employee->EmployeePosition ? $employee->EmployeePosition->Title ? $employee->EmployeePosition->Title->Sym : '' :'';
                $employeeManager = $employee->EmployeePosition ? $employee->EmployeePosition->Manager ? $employee->EmployeePosition->Manager->FirstName . ' ' . $employee->EmployeePosition->Manager->LastName  : '' :'';

                //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
                array_push($values, $employeeDepartment);
                array_push($values, $employeeServiceCode);
                array_push($values, $employeeRegion);
                array_push($values, $employeeCity);
                array_push($values, $employeeWorkingField);
                array_push($values, $employeeStaffID);
                array_push($values, $employeeFullName);
                array_push($values, $employeeLastName);
                array_push($values, $employeeTitle);
                array_push($values, $employeeManager);
                array_push($values, "Aktif");
                array_push($values, "");//Check-In


                foreach ($columns as $keyColumns => $column) {
                    $columnLetter = chr($asciiCapitalA);
                    $workSheet->setCellValue($columnLetter . ($rowNum), $values[$keyColumns]);
                    $asciiCapitalA++;
                }
                $rowNum++;
            }

            break;


        }

        $workSheet->setCellValue("A1","MS Projesi Kaynak Mevcudiyet Raporu");
        $workSheet->setCellValue("A2",date("d.m.Y - H:i:s"));





        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $spreadsheet->addSheet($workSheet,0);

        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'color' => [
                    'rgb' => 'FFFFFF',
                ]
            ],
        ];

        $workSheet->getStyle('A1:L3')->applyFromArray($styleArray);

        $spreadsheet->setActiveSheetIndex(0);


        $styleArray = [
            'font' => [
                'color' => [
                    'rgb' => 'FFFFFF',
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'color' => [
                    'rgb' => '0b5885',
                ]
            ],
        ];

        $spreadsheet->getActiveSheet()->getStyle('A4:L4')->applyFromArray($styleArray);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("DailyEmployeeStatusReport.xlsx", $content);
        return response()->download(storage_path('app/' . "DailyEmployeeStatusReport.xlsx"));

    }

    public function toExcel(Request $request)
    {

        $employees = EmployeeModel::where("Active",1)->get();
        $spreadsheet = new Spreadsheet();

        $generalInformationsSheet = EmployeeModel::toExcelGeneralInformations($spreadsheet,$employees);
        $positionInformationsSheet = EmployeeModel::toExcelPositionInformations($spreadsheet,$employees);
        $contractInformationsSheet = EmployeeModel::toExcelContractInformations($spreadsheet,$employees);
        $paymentInformationsSheet = EmployeeModel::toExcelPaymentInformations($spreadsheet,$employees);
        $additionalPaymentInformationsSheet = EmployeeModel::toExcelAdditionalPaymentInformations($spreadsheet,$employees);
        $educationInformationSheet = EmployeeModel::toExcelEducationInformations($spreadsheet,$employees);
        $contactInformationSheet = EmployeeModel::toExcelContactInformations($spreadsheet,$employees);
        $addressInformationSheet = EmployeeModel::toExcelAddressInformations($spreadsheet,$employees);
        $agiInformationSheet = EmployeeModel::toExcelAGIInformations($spreadsheet,$employees);
        $childrenInformationSheet = EmployeeModel::toExcelChildrenInformations($spreadsheet,$employees);
        $drivingLicenseInformationSheet = EmployeeModel::toExcelDrivingLicenseInformations($spreadsheet,$employees);
        $psychotechnicInformationSheet = EmployeeModel::toExcelPsychoTechnicInformations($spreadsheet,$employees);
        $srcInformationSheet = EmployeeModel::toExcelSRCInformations($spreadsheet,$employees);
        $emergencyInformationSheet = EmployeeModel::toExcelEmergencyInformations($spreadsheet, $employees);
        $bodyMeasurementsInformationSheet = EmployeeModel::toExcelBodyMeasurementsInformations($spreadsheet, $employees);
        $IDCardInformationSheet = EmployeeModel::toExcelIDCardInformations($spreadsheet, $employees);
        $socialSecurityInformationSheet = EmployeeModel::toExcelSocialSecurityInformations($spreadsheet, $employees);
        $bankInformationSheet = EmployeeModel::toExcelBankInformations($spreadsheet, $employees);

        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $spreadsheet->addSheet($generalInformationsSheet,0);
        $spreadsheet->addSheet($positionInformationsSheet,1);
        $spreadsheet->addSheet($contractInformationsSheet,2);
        $spreadsheet->addSheet($paymentInformationsSheet,3);
        $spreadsheet->addSheet($additionalPaymentInformationsSheet,4);
        $spreadsheet->addSheet($educationInformationSheet,5);
        $spreadsheet->addSheet($contactInformationSheet,6);
        $spreadsheet->addSheet($addressInformationSheet,7);
        $spreadsheet->addSheet($agiInformationSheet,8);
        $spreadsheet->addSheet($childrenInformationSheet,9);
        $spreadsheet->addSheet($drivingLicenseInformationSheet,10);
        $spreadsheet->addSheet($psychotechnicInformationSheet,11);
        $spreadsheet->addSheet($srcInformationSheet,12);
        $spreadsheet->addSheet($emergencyInformationSheet,13);
        $spreadsheet->addSheet($bodyMeasurementsInformationSheet,14);
        $spreadsheet->addSheet($IDCardInformationSheet,15);
        $spreadsheet->addSheet($socialSecurityInformationSheet,16);
        $spreadsheet->addSheet($bankInformationSheet,17);


        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("Employees.xlsx", $content);
        return response()->download(storage_path('app/' . "Employees.xlsx"));
    }

    public function searchEmployees(Request $request)
    {

        $page = ($request->Page - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;
        $searchText = $request->SearchText;

        $employeesQ = $request->isHrEmployee == 'true' ? EmployeeModel::whereIn("Active",[1,0]) : EmployeeModel::where(['Active' => 1])->where("Id",">=",753)->where("Id","!=","1636");
        $employeesQ = $employeesQ->where(function ($query) use ($searchText) {
            $query->orWhere(DB::table("Employee")->raw("CONCAT_WS(' ', LastName, UsageName)"), 'like', '%'.$searchText.'%');
            $query->orWhere(DB::table("Employee")->raw("CONCAT_WS(' ', UsageName, LastName)"), 'like', '%'.$searchText.'%');
        });

        $dataCount = $employeesQ->count();
        $employeesQ = $employeesQ->offset($page)->take($recordPerPage)->orderBy("UsageName","asc");
        $employees = $employeesQ->get();

        foreach ($employees as $employee) {
            $countsOfPositions = DB::table("EmployeePosition")->where("EmployeeID", $employee->Id)->whereIn("Active", [1, 2])->count();
            $countsOfPayments = PaymentModel::where(["EmployeeID" => $employee->Id, "Active" => 1])->count();
            $countsOfContractType = EmployeeModel::where(['Id' => $employee->Id])->whereNotNull("ContractTypeID")->count();

            if ($employee->Active == 0)
                $statusVal = "İşten Ayrılan";
            else if ($countsOfPositions < 1 || $countsOfPayments < 1 || $countsOfContractType < 1)
                $statusVal = "Çalışan Adayı";
            else
                $statusVal = "Aktif Çalışan";

            $employee->StatusVal = $statusVal;

            $isHavePermit = PermitModel::where(['EmployeeID' => $employee->Id, 'active' => 1])
                ->whereDate('start_date', '<=', date("Y-m-d"))
                ->whereDate('end_date', '>=', date("Y-m-d"))
                ->where("status",">=",3)
                ->where("manager_status", "!=", 2)
                ->where("hr_status", "!=", 2)
                ->where("ps_status", "!=", 2)
                ->first();

            $isHaveReport = HealthReportModel::where(['Active' => 1, 'EmployeeID' => $employee->Id])
                ->whereDate('start_date', '<=', date("Y-m-d"))
                ->whereDate('end_date', '>=', date("Y-m-d"))
                ->first();

            $employee->Permit = $isHavePermit;
            $employee->HealthReport = $isHaveReport;

        }


        if (count($employees) > 0)
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $employees,
                'dataCount' => $dataCount
            ],200);
        }
        else
            return response([
                'status' => false,
                'message' => 'Sonuç Bulunamadı',
                'dataCount' => $dataCount
            ],200);

    }

    public function allEmployees(Request $request)
    {

        $page = ($request->Page - 1) * $request->RecordPerPage;
        $recordPerPage = $request->RecordPerPage;

        $loggedUserHasGroup = EmployeeHasGroupModel::where(['EmployeeID' => $request->Employee, 'active' => 1])->whereIn('group_id',[17,18])->count();

        $employeesRegularIDList = [];

        if ($loggedUserHasGroup < 1)
        {

            $employeesQ2 = DB::table("Employee")->where('Active', 1);
            $employees = $employeesQ2->get();

            foreach ($employees as $employee) {
                $countsOfPositions = DB::table("EmployeePosition")->where("EmployeeID", $employee->Id)->whereIn("Active", [1, 2])->count();
                $countsOfPayments = PaymentModel::where(["EmployeeID" => $employee->Id, "Active" => 1])->count();
                $countsOfContractType = EmployeeModel::where(['Id' => $employee->Id])->whereNotNull("ContractTypeID")->count();

                if ($countsOfPositions > 0 && $countsOfPayments > 0 && $countsOfContractType > 0)
                    array_push($employeesRegularIDList,$employee->Id);
            }

            $employeesQ3 = EmployeeModel::whereIn('Id',$employeesRegularIDList);
            $employeesCount = $employeesQ3->count();
            $employees = $employeesQ3->offset($page)->take($recordPerPage)->orderBy("UsageName","asc")->get();
            $dataCount = $employeesCount;

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $employees,
                'dataCount' => $dataCount
            ], 200);
        }

        $employeesQ = EmployeeModel::offset($page)->take($recordPerPage)->orderBy("UsageName","asc");
        $employees = $employeesQ->get();
        $dataCount = DB::table("Employee")->count();

        foreach ($employees as $employee) {
            $countsOfPositions = DB::table("EmployeePosition")->where("EmployeeID", $employee->Id)->whereIn("Active", [1, 2])->count();
            $countsOfPayments = PaymentModel::where(["EmployeeID" => $employee->Id, "Active" => 1])->count();
            $countsOfContractType = EmployeeModel::where(['Id' => $employee->Id])->whereNotNull("ContractTypeID")->count();

            if ($employee->Active == 0)
                $statusVal = "İşten Ayrılan";
            else if ($countsOfPositions < 1 || $countsOfPayments < 1 || $countsOfContractType < 1)
                $statusVal = "Çalışan Adayı";
            else
                $statusVal = "Aktif Çalışan";

            $employee->StatusVal = $statusVal;

            //TODO Sağlık Raporu kısmı eklenecek

            $isHavePermit = PermitModel::where(['EmployeeID' => $employee->Id])
                ->whereDate('start_date', '<=', date("Y-m-d"))
                ->whereDate('end_date', '>=', date("Y-m-d"))
                ->where("status",">=",3)
                ->where("manager_status", "!=", 2)
                ->where("hr_status", "!=", 2)
                ->where("ps_status", "!=", 2)
                ->first();

            $isHaveReport = HealthReportModel::where(['Active' => 1, 'EmployeeID' => $employee->Id])
                ->whereDate('start_date', '<=', date("Y-m-d"))
                ->whereDate('end_date', '>=', date("Y-m-d"))
                ->first();

            $employee->Permit = $isHavePermit;
            $employee->HealthReport = $isHaveReport;


            if ($countsOfPositions > 0 && $countsOfPayments > 0 && $countsOfContractType > 0)
                array_push($employeesRegularIDList,$employee->Id);
        }



        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees,
            'dataCount' => $dataCount,
        ], 200);
    }

    public function employeeFullRecorded(Request $request)
    {


    }

    public function getEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }

    public function getEmployeeById2(Request $request)
    {
        $employee = EmployeeModel::find($request->id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }

    public function saveEmployeesChild(Request $request)
    {
        $result = EmployeesChildModel::saveEmployeesChild($request);

        if ($result)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
            ], 200);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.',
            ], 200);


    }

    public function deleteEmployeesChild(Request $request)
    {
        if (EmployeesChildModel::where('id', $request->childId)->update(['active' => 0])) {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.',
            ], 200);
        }

    }

    public function getEmployeesChildren(Request $request)
    {
        $children = EmployeesChildModel::where('EmployeeID', $request->employeeID)->where('active', 1)->get();
        $fields['genders'] = GenderModel::all();
        $fields['relationships'] = RelationshipDegreeModel::all();
        $fields['educationLevel'] = EducationLevelModel::all();
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı.',
            'data' => $children,
            'fields' => $fields
        ], 200);
    }

    public function addEmployee(Request $request)
    {

        if ($request->staffId != null)
        {
            $count = EmployeeModel::where(['StaffID' => $request->staffId])->count();

            if ($count > 0)
                return response([
                    'status' => false,
                    'message' => 'Girmiş olduğunuz PersonelID halihazırda başka bir kullanıcıda tanımlı bulunuyor.',
                ], 200);
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::addEmployee($request),
            'request' => $request->all()
        ], 200);
    }

    public function deleteEmployee(Request $request)
    {
        $request_data['employeeid'] = $request->all();
        $status = EmployeeModel::deleteEmployee($request['employeeid']);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
        ]);
    }

    public function destroyEmployee(Request $request)
    {
        $status = EmployeeModel::destroy($request->EmployeeID);
        if ($status)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
            ]);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız',
            ]);
    }

    public function getGeneralInformationFields(Request $request)
    {
        $fields = EmployeeModel::getGeneralInformationsFields($request->Employee);

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }

    public function getGeneralInformationsOfEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee,
                'generalInfoFields' => EmployeeModel::getGeneralInformationsFields($id)
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }

    public function saveGeneralInformations(Request $request, $id)
    {

        $employee = EmployeeModel::where('Id', $id)->first();

        $freshData = EmployeeModel::saveGeneralInformations($employee, $request);

        if ($freshData)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ], 200);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ], 200);
    }

    public function saveOtherGeneralInformations(Request $request, $id)
    {
        $employee = EmployeeModel::where('Id', $id)->first();
        $freshData = EmployeeModel::saveOtherInformations($employee, $request);

        if ($freshData)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ], 200);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ], 200);
    }

    public function getContactInformationsOfEmployee($id)
    {
        $employee = EmployeeModel::find($id);

        if ($employee != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $employee
            ], 200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ], 200);

    }

    public function saveContactInformation(Request $request)
    {

        $employee = EmployeeModel::find($request->employeeid);

        $freshData = EmployeeModel::saveContactInformation($employee, $request);

        if ($freshData)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ]);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ]);

    }

    public function sendSMSCode(Request $request)
    {
        $employee = EmployeeModel::find($request->Employee);
        $username = "8503073830"; //
        $password = "N7LERJ4F"; //


        $url= "https://api.netgsm.com.tr/sms/send/get";

        $randomNumber = rand(100000,999999);
        $employee->SMSCode = $randomNumber;
        $employee->save();
        if ($request->SMSType == null || $request->SMSType == '')
        {
            return response([
                'status' => false,
                'message' => 'SMSType alanı boş olamaz'
            ],200);
        }
        $phoneNumber = '';
        switch ($request->SMSType)
        {
            case 'login':
                $message = "Sayın " . $employee->UsageName . ' ' .$employee->LastName .", aSAY Connect erişim şifreniz : ".$randomNumber;
                $phoneNumber = $employee->JobMobilePhone;
                break;
            case 'accessType':
                $message = "Sayın Birsel Nalkıran, ". $request->EmployeeName ." adlı çalışan için ik erişim yetkisi talep edilmiştir. Bu işlemin gerçekleştirilmesi için ". $employee->UsageName.' '.$employee->LastName ." adlı çalışana iletmeniz gereken kod : ".$randomNumber;
                $phoneNumber = '5051095345';
                break;

        }
        $messageHeader = "aSAY";
        $guzzleParams = [
            'query' => [
                'usercode'      => $username,
                'password'      => $password,
                'gsmno'         => $phoneNumber,
                'message'       => $message,
                'msgheader'     => $messageHeader
            ],
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request("GET", $url,$guzzleParams);
        $responseBody = json_decode($res->getBody());

        $responseBodyArray = explode(" ",$responseBody); // 00 => hata kodu 123456 => SMS kontrol kodu

        if ($responseBodyArray[0] == "20")
            return response([
                'status' => false,
                'message' => 'Mesaj karakter sınırını aşıyor.'
            ],200);
        if ($responseBodyArray[0] == "30")
            return response([
                'status' => false,
                'message' => 'API username veya password hatası'
            ],200);
        if ($responseBodyArray[0] == "40")
            return response([
                'status' => false,
                'message' => 'Gönderici adı sistemde kayıtlı değil'
            ],200);
        if ($responseBodyArray[0] == "70")
            return response([
                'status' => false,
                'message' => 'Hatalı parametre gönderdiniz, parametreleri kontrol ediniz'
            ],200);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
        ],200);

    }

    public function verifySMSCode(Request $request)
    {
        $smsCode = EmployeeModel::where(['Id' => $request->Employee, 'SMSCode' => $request->verifyCode])->first();

        if ($smsCode)
        {
            $smsCode->SMSCode = null;
            $smsCode->save();
            return response([
                'status' => true,
                'message' => 'Kod doğru'
            ],200);
        }

        else
            return response([
                'status' => false,
                'message' => 'Kod yanlış'
            ]);

    }

    public function setPropertyValues(Request $request)
    {
        $isPropertyValueSave = EmployeePropertyValuesModel::setPropertyValues($request->Employee,$request->propertyCode,$request->propertyValue);
        if($isPropertyValueSave){
            return response([
                'status' => true,
                'message' => "Başarılı",
                'data' => $request->all()
            ],200);
        } else {
            return response([
                'status' => false,
                'message' => "Hata Oluştu"
            ]);
        }
    }

    public function createLog(Request $request)
    {
        $isLogSave = EmployeeLogsModel::setLog($request->Employee,$request->logType,$request->logValue,$request->logText);
        if($isLogSave){
            return response([
                'status' => true,
                'message' => "Başarılı"
            ],200);
        } else {
            return response([
                'status' => false,
                'message' => "Hata Oluştu"
            ]);
        }
    }

    public function employeeHasCar(Request $request){

        $employeeProperty = EmployeePropertyValuesModel::where(['EmployeeID' =>$request->Employee, 'PropertyCode' => 'CarPlate', 'Active' => 1])
            ->whereDate("CreateDate",date("Y-m-d"))->first();

        if (!$employeeProperty)
        {
            return response([
                'status' => true,
                'message' => 'Araç bilgisi bulunamadı',
                'data' => false
            ],200);
        }

        if ($employeeProperty->PropertyValue == null)
        {
            return response([
                'status' => true,
                'data' => false
            ],200);
        }
        else
        {
            return response([
                'status' => true,
                'data' => true
            ],200);
        }


    }

}
