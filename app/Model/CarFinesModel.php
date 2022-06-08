<?php

namespace App;

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CarFinesModel extends Model
{
    //

    protected $primaryKey = "id";
    protected $table = "CarFines";
}
