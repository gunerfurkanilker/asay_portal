<?php

namespace App\Model;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PayMethodModel extends Model
{
    protected $primaryKey = "Id";
    protected $table ="PayMethod";
    public $timestamps = false;


}
