<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BankModel;
use App\Model\EmployeeBankModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class EmployeeBankController extends ApiController
{
    public function saveEmployeeBank(Request $request)
    {
        $result = EmployeeBankModel::saveEmployeeBank($request);

        if ($result)
            return response([
                'status' => true,
                'message' => "Banka Bilgisi Kaydedildi",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız."
            ], 200);
    }

    public function getBanks(){
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => BankModel::all()
        ],200);
    }

    public function getEmployeeBankInformations(Request $request){

        $data = [];
        $paymentBankAccount     = EmployeeBankModel::where(['EmployeeID' => $request->EmployeeID, 'AccountTypeID' => 1])->first();
        $personalBankInfo       = EmployeeBankModel::where(['EmployeeID' => $request->EmployeeID, 'AccountTypeID' => 2])->first();
        $jobAllowanceAccount    = EmployeeBankModel::where(['EmployeeID' => $request->EmployeeID, 'AccountTypeID' => 3])->first();
        array_push($data,$paymentBankAccount);
        array_push($data,$personalBankInfo);
        array_push($data,$jobAllowanceAccount);

        return response([
            'status'    => true,
            'message'   => 'İşlem Başarılı',
            'data'      => $data
        ],200);

    }
}
