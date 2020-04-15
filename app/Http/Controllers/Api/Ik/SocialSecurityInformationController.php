<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\IdCardModel;
use Illuminate\Http\Request;

class SocialSecurityInformationController extends ApiController
{

    public function saveSocialSecurityInformation(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);

        if (!is_null($employee))
        {
            if ($employee->SocialSecurityInformationID != null)
                $socialSecurityInformation = IdCardModel::saveSocialSecurityInformation($request->all(),$employee->SocialSecurityInformationID);
            else
                $socialSecurityInformation = IdCardModel::addSocialSecurityInformation($request->all(),$employee);

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
}
