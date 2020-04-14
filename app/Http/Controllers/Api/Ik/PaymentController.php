<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AdditionalPaymentModel;
use App\Model\EmployeeModel;
use App\Model\PaymentModel;
use Illuminate\Http\Request;


class PaymentController extends ApiController
{
    public function savePayment(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);

        if (!is_null($employee))
        {

            if (!is_null($employee->PaymentID))
            {
                $salary = PaymentModel::editSalary($request->all(),$employee->PaymentID);
                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı.',
                    'data' => $salary
                ],200);
            }

            else
            {
                $salary = PaymentModel::addSalary($request->all(),$employeeId);
                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı.',
                    'data' => $salary
                ],200);
            }
        }

        else{
            return response([
                'status' => false,
                'message' => 'Kullanıcı Bulunamadı.'
            ],200);
        }
    }

    public function saveAdditionalPayment(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);

        if (!is_null($employeeId))
        {
            $payment = PaymentModel::find($employee->PaymentID);
            if (!is_null($payment))
            {
                $additionalPayment = AdditionalPaymentModel::addAdditionalPayment($request->all(),$payment->Id);
                return response([
                    'status' => true,
                    'message' => "İşlem Başarılı.",
                    'data' => $additionalPayment
                ],200);
            }
            else
            {
                return response([
                    'status' => false,
                    'message' => "Ana Ödeme Olmadan Ek Ödeme Atanamaz."
                ],200);
            }

        }
        else
        {
            return response([
                'status' => false,
                'message' => "Çalışan Bulunamadı."
            ],200);
        }



    }

    public function editAdditionalPayment(Request $request,$additionalPaymentId)
    {

    }

    public function deleteAdditionalPayment($additionalPaymentId)
    {

    }



}
