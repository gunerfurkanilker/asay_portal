<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AdditionalPaymentModel;
use App\Model\EmployeeModel;
use App\Model\PaymentModel;
use Illuminate\Http\Request;


class PaymentController extends ApiController
{

    public function allPayment()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı.',
            'data' => PaymentModel::all()
        ]);
    }

    public function savePayment(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);

        if (!is_null($employee))
        {
            $salary = PaymentModel::find($employee->PaymentID);

            if (!is_null($salary))
            {
                $salary = PaymentModel::editSalary($request->all(),$salary);
                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı.',
                    'data' => $salary
                ]);
            }

            else
            {
                $salary = PaymentModel::addSalary($request->all(),$employeeId);
                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı.',
                    'data' => $salary
                ]);
            }
        }

        else{
            return response([
                'status' => false,
                'message' => 'Kullanıcı Bulunamadı.'
            ]);
        }
    }



}
