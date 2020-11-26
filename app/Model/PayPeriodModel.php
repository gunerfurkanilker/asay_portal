<?php

namespace App\Model;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PayPeriodModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "PayPeriod";
    public $timestamps =false;

}
