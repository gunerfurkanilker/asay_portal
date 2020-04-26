<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\SocialSecurityInformationModel;
use Illuminate\Http\Request;

class SocialSecurityInformationController extends ApiController
{

    public function saveSocialSecurityInformation(Request $request)
    {
        $request_data = $request->all();
        $employee = EmployeeModel::find($request_data['employeeid']);

        if (!is_null($employee))
        {
            if ($employee->SocialSecurityInformationID != null)
                $socialSecurityInformation = SocialSecurityInformationModel::saveSocialSecurityInformation($request_data,$employee->SocialSecurityInformationID);
            else
                $socialSecurityInformation = SocialSecurityInformationModel::addSocialSecurityInformation($request_data,$employee);

            if ($socialSecurityInformation)
                return response([
                    'status' => true,
                    'message' => $socialSecurityInformation->Id . " ID No'lu Sosyal Güvenlik Bilgileri Kaydedildi",
                    'data' => $socialSecurityInformation
                ],200);
            else
                return response([
                    'status' => false,
                    'message' => "İşlem Başarısız."
                ],200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => $employeeId. " ID No'lu Çalışan bulunamadı."
            ],200);
        }
    }

    public function getSSInformations($id){
        $employee = EmployeeModel::find($id);

        if ($employee->SocialSecurityInformationID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => SocialSecurityInformationModel::find($employee->SocialSecurityInformationID)
            ],200);

    }

    public function getSSInformationFields(){
        $fields = SocialSecurityInformationModel::getSSIFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }

}
