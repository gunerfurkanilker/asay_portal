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
        $status = SocialSecurityInformationModel::saveSocialSecurityInformation($request);


        if ($status)
            return response([
                'status' => true,
                'message' => "Sosyal Güvenlik Bilgileri Kaydedildi",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız."
            ], 200);

    }

    public function getSSInformations($id)
    {

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => SocialSecurityInformationModel::where("EmployeeID", $id)->first()
        ], 200);

    }

    public function getSSInformationFields()
    {
        $fields = SocialSecurityInformationModel::getSSIFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }

}
