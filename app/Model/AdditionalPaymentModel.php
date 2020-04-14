<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class AdditionalPaymentModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "AdditionalPayment";
    protected $appends =[

    ];

    public static function addAdditionalPayment($request,$salaryId)
    {

    }

}
