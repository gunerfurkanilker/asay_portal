<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AdditionalPaymentModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\PaymentModel;
use Illuminate\Http\Request;


class PaymentController extends ApiController
{

    public function getPaymentsOfEmployee($id)
    {
        $employee = EmployeeModel::find($id);

        if (!is_null($employee))
        {
                $salaries = PaymentModel::getSalaries($employee->Id);
                if ($salaries)
                    return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı.',
                    'data' => $salaries
                ],200);

                else
                    return response([
                    'status' => false,
                    'message' => 'İşlem Başarısız.'
                ],200);
        }
        else{
            return response([
                'status' => false,
                'message' => 'Kullanıcı Bulunamadı.'
            ],200);
        }
    }

    public function getAdditionalPaymentsOfPayment($paymentId)
    {
        $payment = PaymentModel::find($id);

        if (!is_null($payment))
        {
            $additionalPayments = PaymentModel::getAdditionalPayments($payment->Id);
            if ($additionalPayments)
                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı.',
                    'data' => $additionalPayments
                ],200);

            else
                return response([
                    'status' => false,
                    'message' => 'İşlem Başarısız.'
                ],200);
        }
        else{
            return response([
                'status' => false,
                'message' => 'Kullanıcı Bulunamadı.'
            ],200);
        }
    }

    public function getPaymentInformationFields(){
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı.',
            'data' => PaymentModel::getPaymentInformationFields()
        ],200);
    }

    public function savePayment(Request $request){

        $currentPayment = PaymentModel::where(["EndDate" => null, 'EmployeeID' => $request->EmployeeID])->first();

        if ($currentPayment)
        {

            $currentPaymentStartDate = new \DateTime($currentPayment->StartDate);
            $requestStartDate = new \DateTime($request->StartDate); //from database



            if($requestStartDate->format("Y-m-d") < $currentPaymentStartDate->format("Y-m-d")) {
                return response([
                    'status' => false,
                    'message' => 'Maaş başlangıç tarihi, bir önceki maaş başlangıç tarihinden eski bir tarihte girilemez',
                ],200);
            }
        }

        $salary = PaymentModel::savePayment($request);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
        ],200);

    }

    public function editPayment(Request $request,$employeeId,$paymentId)
    {
        $requestData = $request->all();
        $paymentOfEmployee = PaymentModel::where('EmployeeID',$employeeId)->where('Id',$paymentId)->first();

        $freshData = PaymentModel::editPayment($paymentOfEmployee,$requestData);

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

    public function deletePayment(Request $request)
    {
        $request = $request->all();
        $status = PaymentModel::deletePayment($request['paymentid']);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $status
        ]);

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
