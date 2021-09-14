<?php

namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class QrModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "qr";
    protected $guarded=[];
    public $timestamps = true;

    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class,'EmployeeID');
    }


}



