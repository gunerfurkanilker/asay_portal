<?php

namespace App\Model;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class LowerBodyModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'LowerBody';
    public $timestamps =false;

}
