<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Library\Asay;
use App\Model\EducationLevelModel;
use App\Model\Employee;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\EmployeesChildModel;
use App\Model\GenderModel;
use App\Model\ProcessesSettingsModel;
use App\Model\RelationshipDegreeModel;
use App\Model\UserModel;
use Illuminate\Http\Request;


class EmployeeController extends ApiController
{

    public function allEmployees()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::all()
        ], 200);
    }

    public function getEmployeesActiveDirectoryAccount(Request $request)
    {
        $employeeUserId = $request->activeDirectoryUserId;

        $user = UserModel::find($employeeUserId);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $user
        ],200);

    }


    public function sendActiveDirectoryCreateRequestMail(Request $request)
    {
        $loggedUser = UserModel::find($request->userId);
        $loggedUserEmployee = EmployeeModel::find($loggedUser->EmployeeID);
        $loggedUserEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $loggedUserEmployee->Id])->first();

        $ITManager = ProcessesSettingsModel::where(['object_type' => 10, 'PropertyCode' => 'ITManager',
            'RegionID' => $loggedUserEmployeePosition->RegionID])->first();
        $ITManagerUser = UserModel::where(['EmployeeID' => $ITManager->PropertyValue])->first();
        Asay::sendMail($ITManagerUser->email,"",'Active Directory Kullanıcısı Oluşturma İsteği',
            'Sayın ' . $ITManagerUser->full_name . ', ' . $request->employee['FirstName'] .' ' . $request->employee['LastName']. ' adlı çalışma arkadaşımız için active directory hesabı oluşturulması talep edilmektedir. Çalışma arkadaşımızın tercih ettiği ismi ' .$request->employee['UsageName'].' olup, ' . $request->requestedMail . ' mail hesabı ile ilgili hesabı oluşturmanız standartlarmıza uygun olacaktır.',"Active Directory Kullanıcısı Oluşturma İsteği","");
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı'
        ],200);
    }

    public function getEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);
        $employee->ActiveDirectoryUserId = UserModel::where(['EmployeeID' => $employee->Id])->first()->Id;

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
        if(EmployeesChildModel::where('id',$request->childId)->update(['active' => 0]))
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
            ], 200);
        }
        else{
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.',
            ], 200);
        }

    }

    public function getEmployeesChildren(Request $request)
    {
        $children = EmployeesChildModel::where('EmployeeID',$request->employeeID)->where('active',1)->get();
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
        $request_data = $request->all();
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => EmployeeModel::addEmployee($request_data)
        ], 200);
    }

    public function deleteEmployee(Request $request) {
        $request_data['employeeid'] = $request->all();
        $status = EmployeeModel::deleteEmployee($request['employeeid']);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
        ]);
    }

    public function getGeneralInformationFields(Request $request){
        $fields = EmployeeModel::getGeneralInformationsFields($request->Employee);

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }

    public function getGeneralInformationsOfEmployeeById($id)
    {
        $employee = EmployeeModel::find($id);
        $activeDirectoryUser = UserModel::where(['EmployeeID' => $employee->Id])->first();
        if ($activeDirectoryUser)
            $employee->setAttribute('ActiveDirectoryUser',$activeDirectoryUser);

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
        $requestData = $request->all();
        $employee = EmployeeModel::where('Id', $id)->first();

        $freshData = EmployeeModel::saveGeneralInformations($employee, $requestData);

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
        $requestData = $request->all();
        $employee = EmployeeModel::where('Id', $id)->first();

        $freshData = EmployeeModel::saveOtherInformations($employee, $requestData);

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

        $requestData = $request->all();
        $employee = EmployeeModel::find($requestData['employeeid']);

        $freshData = EmployeeModel::saveContactInformation($employee, $requestData);

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

}
