<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AgiModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class AGIController extends ApiController
{

    public function saveAgi(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);
        if (!is_null($employee))
        {
            if ($employee->AGIID != null)
                $agi = AgiModel::saveAgi($request->all(),$employee->AGIID);
            else
                $agi = AgiModel::addAgi($request->all(),$employee);

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
        }
    }
}
