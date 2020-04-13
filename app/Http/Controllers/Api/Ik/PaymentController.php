<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\PaymentModel;
use http\Client\Request;

class PaymentController extends ApiController
{

    public function addSalary(Request $request,$id)
    {
        $employee = PaymentModel::addSalary($request->all(),$id);

        if ($employee)
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $employee
            ]);
        }

    }

    public function addPayment(Request $request,$id)
    {

    }

}
