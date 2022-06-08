<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\DepartmentModel;
use App\Model\EmployeeModel;
use App\Model\EmployeeTrainingModel;
use App\Model\RegionModel;
use App\Model\SocialSecurityInformationModel;
use App\Model\TrainingCategoryModel;
use App\Model\TrainingCompanyModel;
use App\Model\TrainingModel;
use App\Model\TrainingPeriodModel;
use App\Model\TrainingResultModel;
use App\Model\TrainingStatusModel;
use App\Model\TrainingTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TrainingController extends ApiController
{
    //


    public function mailToIsgNewEmployee(Request $request){
        EmployeeTrainingModel::mailToIsgNewEmployee($request);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı'
        ],200);

    }

    public function saveTrainingCategory(Request $request){

        $result = TrainingCategoryModel::saveTrainingCategory($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız'
        ],200);

    }

    public function saveTraining(Request $request)
    {

        $result = TrainingModel::saveTraining($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız'
        ],200);

    }

    public function saveCompany(Request $request)
    {

        $result = TrainingModel::saveISGCompany($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız',
            'test' => $request->all()
        ],200);

    }

    public function saveEmployeeTraining(Request $request){

        $isEmployeeExists = EmployeeModel::where(['Active' => 1, 'Id' => $request->EmployeeID])->first();
        if(!$isEmployeeExists)
            return response([
                'status' => false,
                'message' => "Eğitim eklemesi yapılması istenen çalışan bulunamadı"
            ],200);

        /*$isTrainingCategoryExistsForEmployee = EmployeeTrainingModel::isTrainingExistAtEmployee($request);

        if(!$isTrainingCategoryExistsForEmployee && $request->CreateType == 0)
            return response([
                'status' => false,
                'message' => "Bu personel için eklenmek istenen eğitim tipi sistemde mevcuttur, var olan eğitimi tekrarlayabilir veya yenileyebilirsiniz."
            ],200);*/

        $result = EmployeeTrainingModel::saveEmployeeTraining($request);

        return response([
            'status' => $result,
            'message' => $result ? 'Kayıt Başarılı' : 'Kayıt Başarısız',
        ],200);

    }



    public function deleteEmployeeTraining(Request $request){
        $request->validate([
         'TrainingID'=>'required',
            'EmployeeID'=>'required'
        ]);



        $result = EmployeeTrainingModel::where(['Active' => 1, 'EmployeeID'=>$request->EmployeeID,'TrainingID' => $request->TrainingID])->update(['active' => 0]);
        return response([
            'status' => $result,
            'message' => $result ? 'İşlem Başarılı' : 'İşlem Başarısız',
        ],200);

    }


    public function getTrainingsToExcel(Request $request){
        set_time_limit(600);
        $filters = $request->filters;
        $employeeID = $filters['EmployeeID'];
        $active = $employeeID ? 0:1;
       // $filters['DepartmentID']=$request->addFilters["DepartmentID"] ?? '';
     //   $filters['RegionID']=$request->addFilters["RegionID"] ?? '';
        $trainings = EmployeeTrainingModel::getTrainings($filters,$employeeID,null,null,$active);
        $trainings = $trainings['trainings'];


        $spreadsheet = new Spreadsheet();

        $spreadsheet->removeSheetByIndex(0); // İlk Sheet'i siliyorum.

        $workSheet = new Worksheet();

        $columns = [
            'Kayıt No',
            'Üst Kayıt No',
            'Personel ID',
            'Çalışan Adı Soyadı',
            'TC Kimlik No',
            'SGK Sicil No',
            'SGK No',
            'Şirkete Giriş Tarihi',
            'Ünvanı',
            'Birimi',
            'Bulunduğu Bölge',
            'Eğitim Adı',
            'Eğitimi Veren Kurum',
            'Eğitim Statüsü',
            'Eğitim Veriliş Tarihi',
            'Eğitim Son Geçerlilik Tarihi',
            'Eğitim Açıklaması',
            'Kayıt Oluşturulma Tarihi',
            'Eğitim Sonucu',
            'Kayıt Durumu'
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

        foreach ($trainings as $key => $training)
        {
            //ASCII "A" harfi 65'ten başlar, "Z" harfi 90 koduyla biter
            $ssiRecord = SocialSecurityInformationModel::where(['EmployeeID' => $training['Employee']['Id']])->first();
            $asciiCapitalA = 65;
            $values = [];
            $kayitNo = $training['id'] ;
            $parentNo = $training['Parent'];


            $personelId = $training['Employee'] ? $training['Employee']['StaffID'] : 'Tanımlı Değil';
            $tckn = $training['Employee'] ? $training['Employee']['IDCard'] ? $training['Employee']['IDCard']['TCNo'] : 'Tanımlı Değil' : 'Tanımlı Değil';
            $sgkno = isset($ssiRecord) &&$ssiRecord->SSIRecordObject ? $ssiRecord->SSIRecordObject->Name : 'Tanımlı Değil';
            $sgkRecord = isset($ssiRecord) &&$ssiRecord->SSIRecordObject ? $ssiRecord->SSINo : 'Tanımlı Değil';
            $startDate = $training['Employee'] ? $training['Employee']['PositionStartDate'] : 'Tanımlı Değil';
            $employeeName = $training['Employee'] ? $training['Employee']['UsageName'] . ' ' .  $training['Employee']['LastName'] : '';
            $employeeTitle = $training['Employee'] ? $training['Employee']['EmployeePosition'] ? $training['Employee']['EmployeePosition']['Title']['Sym'] : '' : '';
            $employeeDepartment = $training['Employee'] ? $training['Employee']['EmployeePosition'] ? $training['Employee']['EmployeePosition']['Department']['Sym'] : '' : '';
            $employeeRegion = $training['Employee'] ? $training['Employee']['EmployeePosition'] ? $training['Employee']['EmployeePosition']['Region']['Name'] : '' : '';
            $trainingName = $training['Training']['Category']['Name'];
            $trainingCompanyName = $training['Training']['Company']['Name'];
            $trainingStartDate = $training['StartDate'];
            $trainingExpireDate = $training['ExpireDate'];
            $trainingDescription = $training['TrainingDescription'];
            $trainingCreateDate = $training['CreateDate'];
            $trainingResult = $training['Result']['Name'];
            $trainingStatus = $training['Status']['Name'];
            $recordStatus = $training['Active'] == 1 ? 'Asıl Kayıt' : 'Arşiv Kayıt';
            //TODO DİKKAT VALUES DİZİSİNE DEĞERLER SIRA İLE EKLENMELİDİR. SÜTUN VE DEĞERLER EŞLEŞECEK ŞEKİLDE
            array_push($values, $kayitNo);
            array_push($values, $parentNo);
            array_push($values, $personelId);
            array_push($values, $employeeName);
            array_push($values, $tckn);
            array_push($values, $sgkRecord);
            array_push($values, $sgkno);
            array_push($values, $startDate);
            array_push($values, $employeeTitle);
            array_push($values, $employeeDepartment);
            array_push($values, $employeeRegion);
            array_push($values, $trainingName);
            array_push($values, $trainingCompanyName);
            array_push($values, $trainingStatus);
            array_push($values, $trainingStartDate);
            array_push($values, $trainingExpireDate);
            array_push($values, $trainingDescription);
            array_push($values, $trainingCreateDate);
            array_push($values, $trainingResult);
            array_push($values, $recordStatus);


            foreach ($columns as $keyColumns => $column)
            {
                $columnLetter = chr($asciiCapitalA);
                $workSheet->setCellValue($columnLetter.($key+2),$values[$keyColumns]);
                $asciiCapitalA++;
            }

        }

        $spreadsheet->addSheet($workSheet,0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        Storage::disk('')->put("Employees.xlsx", $content);
        return response()->download(storage_path('app/' . "Employees.xlsx"));


    }

    public function getDepartments()
    {
        return response(['data'=>DepartmentModel::where('Active',1)->get()]);
    }

    public function getRegions()
    {
        return response(['data'=>RegionModel::where('Active',1)->get()]);

    }

    public function getEmployeeTrainings(Request $request){
        $filters = $request->filters;
        $employeeID = $request->EmployeeID;
        $rowPerPage = $request->rowsPerPage;
        $filters['DepartmentID']=$request->addFilters["DepartmentID"] ?? '';
        $filters['RegionID']=$request->addFilters["RegionID"] ?? '';
        $page = $request->page;
       // return response(EmployeeTrainingModel::getTrainings($filters,$employeeID,$page,$rowPerPage));
        $data = EmployeeTrainingModel::getTrainings($filters,$employeeID,$page,$rowPerPage);
        $totalCount = $data['count'];
        $paginationNumMax =  $rowPerPage && $data['trainings'] && $totalCount > 0 ? (int) ($totalCount / $rowPerPage) : 1;
        $paginationNumMax = $totalCount % $rowPerPage != 0 ? $paginationNumMax + 1 : $paginationNumMax ;

        return response([
            'message' => 'İşlem Başarılı',
            'data' => $data['trainings'],
            'addFilters' => $data['addFilters'],
            'filters' => $filters,
            'paginationNumMax' => $paginationNumMax,
            'page' => $page,
            'rowsPerPage' => $rowPerPage,
            'count' => $totalCount
        ],200);

    }

    public function getTrainingsParents(Request $request){

        if($request->Root == 0)
            return response([
                'status' => false,
                'message' => 'Bu kaydın alt kayıtları bulunmamaktadır.'
            ],200);
        $counter = 0;
        $training = EmployeeTrainingModel::find($request->id);
        $root = $training->Root;
        $parentId = $training->Parent;
        $parentList = [];
        while($root != 0)
        {
            $counter++;
            $root--;
            $parent = EmployeeTrainingModel::where(['id' =>$parentId, 'Root' => $root])->first();
            if($parent)
            {
                array_push($parentList,$parent);
                $parentId = $parent->Parent;
            }
        }

        return response([
            'status' => true,
            'data' => $parentList,
            'test' => $counter
        ],200);
    }

    public function getTrainings(Request $request){


        $trainingsQ = TrainingModel::where(["Active" => 1]);

        if($request->CompanyID)
        {
            $trainingsQ->where("CompanyID",$request->CompanyID);
        }


        $trainings = $trainingsQ->get();

        return response([
            'status' => true,
            'data' => $trainings
        ],200);

    }

    public function getEmployees(Request $request){

        $employees = DB::table("Employee")->where(['Active' => 1])->get();

        foreach ($employees as $employee)
        {
            $employee->SocialSecurityInformation = null;
            $ssi = SocialSecurityInformationModel::where(['EmployeeID' => $employee->Id])->first();
            if ($ssi)
                $employee->SocialSecurityInformation = $ssi;

        }

        return response([
            'status' => true,
            'data' => $employees
        ],200);

    }

    public function getCompanies(Request $request){

        $companies = TrainingCompanyModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $companies
        ],200);

    }

    public function getStatuses(Request $request){

        $statuses = TrainingStatusModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $statuses
        ],200);

    }

    public function getISGResults(Request $request){

        $results = TrainingResultModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $results
        ],200);

    }

    public function getCategories(Request $request){

        $categories = TrainingCategoryModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $categories
        ],200);

    }

    public function getTrainingTypes(Request $request){

        $trainingTypes = TrainingTypeModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $trainingTypes
        ],200);

    }

    public function getTrainingPeriodsOfTraining(Request $request){

        $data = TrainingPeriodModel::getPeriodOfTraining($request);

        return response($data,200);

    }

    //Süresinin dolmasına yaklaşan ve dolan kayıtların mail olarak göndemi
    public function sendExpiredTrainingsMailToIsgEmployees(){
        EmployeeTrainingModel::sendExpiredTrainingsMailToIsgEmployees();
    }
    public function sendExpiredTrainingsMailToIsgEmployees2(){
        EmployeeTrainingModel::sendExpiredTrainingsMailToIsgEmployees2();
    }


}
