<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class AdditionalPaymentModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "AdditionalPayment";
    protected $appends =[
        "AdditionalPaymentType",
        "PayPeriod",
        "Currency"
    ];

    public static function addAdditionalPayment($request,$salaryId)
    {

    }

    public function getAdditionalPaymentTypeAttribute()
    {
        $additionalPaymentType = $this->hasOne(AdditionalPaymentTypeModel::class,"Id","AdditionalPaymentTypeID");
        return $additionalPaymentType->where("Active","1")->first();
    }

    public function getPayPeriodAttribute()
    {
        $payPeriod = $this->hasOne(PayPeriodModel::class,"Id","PayPeriodID");
        return $payPeriod->where("Active","1")->first();
    }

    public function getCurrencyAttribute()
    {
        $currency = $this->hasOne(CurrencyModel::class,"Id","CurrencyID");
        return $currency->where("Active","1")->first();
    }

}
