<?php

namespace App\Model;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class BodyMeasurementModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "BodyMeasurements";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'UpperBody',
        'LowerBody',
        'ShoeSize'
    ];

    public static function saveBodyMeasurements($request)
    {

        $bodyMeasurement = self::where(['EmployeeID' => $request->EmployeeID])->first();
        if (!$bodyMeasurement)
            $bodyMeasurement = new BodyMeasurementModel();


        $bodyMeasurement->EmployeeID = $request->EmployeeID;
        $bodyMeasurement->UpperBody = $request->UpperBody;
        $bodyMeasurement->LowerBody = $request->LowerBody;
        $bodyMeasurement->ShoeSize = $request->ShoeSize;


        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($request->EmployeeID);
        LogsModel::setLog($request->Employee,$bodyMeasurement->Id,15,51,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın, giyim aksesuar bilgisini düzenledi","","","","","");

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
    public function setUpperBodyAttribute($value)
    {
        $this->attributes['UpperBody'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getUpperBodyAttribute($value)
    {
        try {
            return $this->attributes['UpperBody'] !== null || $this->attributes['UpperBody'] != '' ? (int) Crypt::decryptString($this->attributes['UpperBody']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setLowerBodyAttribute($value)
    {
        $this->attributes['LowerBody'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getLowerBodyAttribute($value)
    {
        try {
            return $this->attributes['LowerBody'] !== null || $this->attributes['LowerBody'] != '' ? (int) Crypt::decryptString($this->attributes['LowerBody']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setShoeSizeAttribute($value)
    {
        $this->attributes['ShoeSize'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getShoeSizeAttribute($value)
    {
        try {
            return $this->attributes['ShoeSize'] !== null || $this->attributes['ShoeSize'] != '' ? (int) Crypt::decryptString($this->attributes['ShoeSize']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

}
