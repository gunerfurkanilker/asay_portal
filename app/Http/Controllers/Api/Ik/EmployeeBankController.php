<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeBankModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class EmployeeBankController extends ApiController
{
    public function saveEmployeeBank(Request $request, $employeeId)
    {
        $employee = EmployeeModel::find($employeeId);
        if (!is_null($employee)) {
            if ($employee->EmployeeBankID != null)
                $employeeBank = EmployeeBankModel::saveEmployeeBank($request->all(), $employee->EmployeeBankID);
            else
                $employeeBank = EmployeeBankModel::addEmployeeBank($request->all(), $employee);

            if ($employeeBank)
                return response([
                    'status' => true,
                    'message' => $employeeBank->Id . " ID No'lu Banka Bilgisi Kaydedildi",
                    'data' => $employeeBank
                ], 200);
            else
                return response([
                    'status' => false,
                    'message' => "İşlem Başarısız."
                ], 200);
        } else {
            return response([
                'status' => false,
                'message' => $employeeId . " ID No'lu Çalışan bulunamadı."
            ], 200);
        }
    }
}
