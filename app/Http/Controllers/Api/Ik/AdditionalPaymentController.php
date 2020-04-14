<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AdditionalPaymentModel;
use App\Model\PaymentModel;
use Illuminate\Http\Request;

class AdditionalPaymentController extends ApiController
{
    public function saveAdditionalPayment(Request $request,$salaryId,$additionalPaymentId = null)
    {

        $salary = PaymentModel::find($salaryId);
        $additionalPayment = false;
        if (!is_null($salary))
        {
           $additionalPayment = AdditionalPaymentModel::addAdditionalPayment($request->all(),$salaryId);
        }


        if ($additionalPayment)
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $salary
            ]);
        }

        else
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.'
            ]);
        }

    }
}
