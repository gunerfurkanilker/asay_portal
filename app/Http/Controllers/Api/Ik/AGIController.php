<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AgiModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use Illuminate\Http\Request;

class AGIController extends ApiController
{

    public function saveAgi(Request $request)
    {
        $request_data = $request->all();
        $employee = EmployeeModel::find($request_data['employeeid']);
        /*if (!is_null($employee))
        {
            if ($employee->AGIID != null)
                $agi = AgiModel::saveAgi($request_data,$employee->AGIID);
            else
                $agi = AgiModel::addAgi($request_data,$employee);

            if ($agi)
                return response([
                    'status' => true,
                    'message' => $agi->Id . " ID No'lu AGİ Bilgisi Kaydedildi",
                    'data' =>$agi
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
        }*/
        return response([
            'status' => false,
            'message' => 'Zaa'
        ],200);
    }

    public function getAgiInformations($id){
        $employee = EmployeeModel::find($id);

        if ($employee->AGIID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => AgiModel::find($employee->AGIID)
            ],200);

    }

    public function getAGIInformationFields(){
        $fields = AgiModel::getAGIFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }

}
