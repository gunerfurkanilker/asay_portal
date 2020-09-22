<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BodyMeasurementModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "BodyMeasurements";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'UBody',
        'LBody',
        'SSize'
    ];

    public static function saveBodyMeasurements($request)
    {
        if ($request->BodyMeasurementID != null)
            $bodyMeasurement = self::find($request->BodyMeasurementID)->first();
        else
            $bodyMeasurement = new BodyMeasurementModel();

        $bodyMeasurement->EmployeeID = $request->EmployeeID;
        $bodyMeasurement->UpperBody = $request->UpperBody;
        $bodyMeasurement->LowerBody = $request->LowerBody;
        $bodyMeasurement->ShoeSize = $request->ShoeSize;


        return $bodyMeasurement->save();

    }

    public static function addBodyMeasurements($request, $employee)
    {

        $bodyMeasurement = self::create([
            'UpperBody' => $request['upperbody'],
            'LowerBody' => $request['lowerbody'],
            'ShoeSize' => $request['shoesize']
        ]);

        if ($bodyMeasurement != null) {
            $employee->BodyMeasurementID = $bodyMeasurement->Id;
            $employee->save();
            return $bodyMeasurement;
        } else
            return false;
    }

    public static function getBodyMeasurementFields()
    {
        $data = [];
        $data['UpperBodies'] = UpperBodyModel::all();
        $data['LowerBodies'] = LowerBodyModel::all();
        $data['ShoeSizes'] = ShoeSizeModel::all();

        return $data;
    }

    public function getUBodyAttribute()
    {
        $upperBody = $this->hasOne(UpperBodyModel::class, "Id", "UpperBody");
        return $upperBody->where("Active", 1)->first();
    }

    public function getLBodyAttribute()
    {
        $lowerBody = $this->hasOne(LowerBodyModel::class, "Id", "LowerBody");
        return $lowerBody->where("Active", 1)->first();
    }

    public function getSSizeAttribute()
    {
        $shoeSize = $this->hasOne(ShoeSizeModel::class, "Id", "ShoeSize");
        return $shoeSize->where("Active", 1)->first();
    }

}
