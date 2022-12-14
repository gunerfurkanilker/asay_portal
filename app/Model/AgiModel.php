<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AgiModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'AGI';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'MaritalStatus',
        'SpouseWorkingStatus',
        'MaritalStatusID',
        'SpouseWorkingStatusID'

    ];

    public static function saveAgi($request, $agiID)
    {
        $agiID = self::find($agiID);

        if ($agiID != null) {
            $agiID->EmployeeID = $request->employeeid;
            $agiID->MaritalStatusID = $request->maritalstatus;
            $agiID->SpouseWorkingStatusID = isset($request->spouseworkingstatus)  ?  $request->spouseworkingstatus:null ;
            $agiID->TotalChildren = $request->totalchildren;
            $agiID->PrePrimaryChild = $request->preprimarychild;
            $agiID->PrimaryChild = $request->primarychild;
            $agiID->MiddleSchoolChild = $request->middleschoolchild;
            $agiID->HighSchoolChild = $request->highschoolchild;
            $agiID->UniversityChild = $request->universitychild;

            $loggedUser = DB::table("Employee")->find($request->Employee);
            $employee = DB::table("Employee")->find($request->employeeid);
            $dirtyFields = $agiID->getDirty();
            foreach ($dirtyFields as $field => $newdata) {
                $olddata = $agiID->getOriginal($field);
                if ($olddata != $newdata) {
                    LogsModel::setLog($request->Employee,$agiID->Id,15,47,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın asgari geçim indirimi bilgisini güncelledi","","","",$field,"");
                }
            }

            $agiID->save();

            return $agiID->fresh();
        }
        else
            return false;
    }

    public static function addAgi($request)
    {
        $agiID = new AgiModel();

        $agiID->EmployeeID = $request->employeeid;
        $agiID->MaritalStatusID = $request->maritalstatus;
        $agiID->SpouseWorkingStatusID = $request->spouseworkingstatus;
        $agiID->TotalChildren = $request->totalchildren;
        $agiID->PrePrimaryChild = $request->preprimarychild;
        $agiID->PrimaryChild = $request->primarychild;
        $agiID->MiddleSchoolChild = $request->middleschoolchild;
        $agiID->HighSchoolChild = $request->highschoolchild;
        $agiID->UniversityChild = $request->universitychild;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($request->employeeid);
        LogsModel::setLog($request->Employee,$agiID->Id,15,47,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın asgari geçim indirimi bilgisini güncelledi","","","",$field,"");

        if ($agiID != null)
        {
            return $agiID;
        }

        else
            return false;
    }

    public static function getAGIFields()
    {
        $data = [];
        $data['MaritalStatus'] = MartialStatusModel::all();
        $data['SpouseWorkingStatus'] = WorkingStatusModel::all();

        return $data;
    }


    public function getMaritalStatusAttribute()
    {
        $maritalStatus = $this->hasOne(MartialStatusModel::class,"Id","MaritalStatusID");
        return $maritalStatus->where("Active",1)->first();
    }

    public function getSpouseWorkingStatusAttribute()
    {
        $spouseWorkingStatus = $this->hasOne(WorkingStatusModel::class,"Id","SpouseWorkingStatusID");
        return $spouseWorkingStatus->where("Active",1)->first();
    }

    public function setMaritalStatusIDAttribute($value)
    {
        $this->attributes['MaritalStatusID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getMaritalStatusIDAttribute($value)
    {
        try {
            return $this->attributes['MaritalStatusID'] !== null || $this->attributes['MaritalStatusID'] != '' ? (int) Crypt::decryptString($this->attributes['MaritalStatusID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setSpouseWorkingStatusIDAttribute($value)
    {
        $this->attributes['SpouseWorkingStatusID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getSpouseWorkingStatusIDAttribute($value)
    {
        try {
            return $this->attributes['SpouseWorkingStatusID'] !== null || $this->attributes['SpouseWorkingStatusID'] != '' ? (int) Crypt::decryptString($this->attributes['SpouseWorkingStatusID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }


}
