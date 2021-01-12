<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Library\Asay;
use App\Model\ContractTypeModel;
use App\Model\EducationLevelModel;
use App\Model\Employee;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\EmployeesChildModel;
use App\Model\GenderModel;
use App\Model\PaymentModel;
use App\Model\ProcessesSettingsModel;
use App\Model\RelationshipDegreeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class EmployeeController extends ApiController
{

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

}
