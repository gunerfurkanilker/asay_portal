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

    public static function saveBodyMeasurements($request, $bodyMeasurementID)
    {
        $bodyMeasurement = self::find($bodyMeasurementID);

        if ($bodyMeasurement != null) {

            $bodyMeasurement->UpperBody = $request['upperbody'];
            $bodyMeasurement->LowerBody = $request['lowerbody'];
            $bodyMeasurement->ShoeSize = $request['shoesize'];


            $bodyMeasurement->save();

            return $bodyMeasurement->fresh();
        }
        else
            return false;
    }

    public static function addBodyMeasurements($request,$employee)
    {

        $bodyMeasurement = self::create([
            'UpperBody' => $request['upperbody'],
            'LowerBody' => $request['lowerbody'],
            'ShoeSize' => $request['shoesize']
        ]);

        if ($bodyMeasurement != null)
        {
            $employee->BodyMeasurementID = $bodyMeasurement->Id;
            $employee->save();
            return $bodyMeasurement;
        }

        else
            return false;
    }

    public function getUBodyAttribute()
    {
        $upperBody = $this->hasOne(UpperBodyModel::class,"Id","UpperBody");
        return $upperBody->where("Active",1)->first();
    }

    public function getLBodyAttribute()
    {
        $lowerBody = $this->hasOne(LowerBodyModel::class,"Id","LowerBody");
        return $lowerBody->where("Active",1)->first();
    }

    public function getSSizeAttribute()
    {
        $shoeSize = $this->hasOne(ShoeSizeModel::class,"Id","ShoeSize");
        return $shoeSize->where("Active",1)->first();
    }

}
