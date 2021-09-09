<?php

namespace App\Model;

use App\Model\EmployeeModel;
use Illuminate\Database\Eloquent\Model;

class PerformanceWeightModel extends Model
{
    protected $primaryKey = "id";
    protected $table = "performanceweight";
    protected $guarded=[];
    public $timestamps = false;

    public function getEmployeePerformanceWeight($request)
    {
        PerformanceWeightModel::where(['EmployeeID' => $request->EmployeeID]);

    }

    public function empoyee()
    {
        return $this->belongsTo(EmployeeModel::class,'EmployeeID');
    }
}
