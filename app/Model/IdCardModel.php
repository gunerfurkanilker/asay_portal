<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class IdCardModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'IDCard';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [

        'Gender',
        'District',
        'Nationality',
        'ObjectFile',
        'BirthDate',
        'ValidDate',
        'DateOfIssue',
        'SerialNumber',
        'FatherName',
        'MotherName',
        'BirthPlace',
        'CityID',
        'DistrictID',
        'Neighborhood',
        'Village',
        'CoverNo',
        'PageNo',
        'RegisterNo',
        'TCNo'

    ];

    public static function saveIDCard($request)
    {
        $employee = EmployeeModel::find($request->EmployeeID);
        if ($employee->IDCardID)
            $IDCard = self::find($employee->IDCardID);
        else
            $IDCard = null;


        if ($IDCard == null)
            $IDCard = new IdCardModel();



            $IDCard->ValidDate = $request->ValidDate != null ? $request->ValidDate : null;
        $IDCard->NewIDCard = $request->NewIDCard === 'true'  || $request->NewIDCard == 1  ? 1 : 0;
        $IDCard->NationalityID = $request->NationalityID;
        $IDCard->TCNo = $request->TCNo;
        $IDCard->FirstName = $request->FirstName;
        $IDCard->LastName = $request->LastName;
        $IDCard->BirthDate = $request->BirthDate;
        $IDCard->GenderID = $request->GenderID;
        $IDCard->SerialNumber = $request->SerialNumber;
        $IDCard->DateOfIssue = $request->DateOfIssue != null ? $request->DateOfIssue : null;
        $IDCard->MotherName = $request->MotherName;
        $IDCard->FatherName = $request->FatherName;
        $IDCard->BirthPlace = $request->BirthPlace ? $request->BirthPlace : null;
        $IDCard->CityID = $request->CityID != null ? $request->CityID : null;
        $IDCard->DistrictID = $request->DistrictID != null ? $request->DistrictID : null;
        $IDCard->Neighborhood = $request->Neighborhood != null ? $request->Neighborhood : '';
        $IDCard->Village = $request->Village != null ? $request->Village : '';
        $IDCard->CoverNo = $request->CoverNo != null ? $request->CoverNo : '';
        $IDCard->PageNo = $request->PageNo != null ? $request->PageNo : '';
        $IDCard->RegisterNo = $request->RegisterNo != null ? $request->RegisterNo : '';
        $result = $IDCard->save();
        $employee->IDCardID = $IDCard->Id;
        $result2 = $employee->save();

        if ($request->hasFile('IDCardPhotocopy'))
            $IDCard->CopyPhoto = self::saveCopyPhoto($request,$IDCard->Id);

        $result = $IDCard->save();

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($request->EmployeeID);
        LogsModel::setLog($request->Employee,$IDCard->Id,15,52,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın, kimlik bilgisini düzenledi","","","","","");

        return $result && $result2 ? true : false;
    }

    public static function saveCopyPhoto($request, $idCardID)
    {
        $file = file_get_contents($request->IDCardPhotocopy->path());
        $guzzleParams = [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $file,
                    'filename' => 'IDCardPhotoCopy' . $idCardID . '.' . $request->IDCardPhotocopy->getClientOriginalExtension()
                ],
                [
                    'name' => 'moduleId',
                    'contents' => 'id_card_copy'
                ],
                [
                    'name' => 'token',
                    'contents' => $request->token
                ]
            ]
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
        $responseBody = json_decode($res->getBody());

        if ($responseBody->status == true) {
            return $responseBody->data;
        }

    }

    public static function getIDCardFields()
    {
        $data = [];
        $data['Nationalities'] = NationalityModel::all();
        $data['Genders'] = GenderModel::all();
        $data['Cities'] = CityModel::all();
        $data['Districts'] = DistrictModel::all();

        return $data;
    }

    public function getGenderAttribute()
    {
        $gender = $this->hasOne(GenderModel::class, "Id", "GenderID");
        return $gender->where("Active", 1)->first();
    }

    public function getDistrictAttribute()
    {
        $district = $this->hasOne(DistrictModel::class, "Id", "GenderID");
        return $district->where("Active", 1)->first();
    }

    public function getNationalityAttribute()
    {
        $nationality = $this->hasOne(NationalityModel::class, "Id", "NationalityID");
        return $nationality->where("Active", 1)->first();
    }

    public function getObjectFileAttribute()
    {
        $objectFile = $this->hasOne(ObjectFileModel::class, 'ObjectId', 'Id');
        return $objectFile->where(['Active' => 1, 'ObjectType' => 7])->first();//Kimlik Fotoğrafı
    }

    public function setTCNoAttribute($value)
    {
        $this->attributes['TCNo'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getTCNoAttribute($value)
    {
        try {
            return $this->attributes['TCNo'] !== null || $this->attributes['TCNo'] != '' ? Crypt::decryptString($this->attributes['TCNo']) : null;;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setBirthDateAttribute($value)
    {
        $this->attributes['BirthDate'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getBirthDateAttribute($value)
    {
        try {
            return $this->attributes['BirthDate'] !== null || $this->attributes['BirthDate'] != '' ? Crypt::decryptString($this->attributes['BirthDate']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setValidDateAttribute($value)
    {
        $this->attributes['ValidDate'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getValidDateAttribute($value)
    {
        try {
            return $this->attributes['ValidDate'] !== null || $this->attributes['ValidDate'] != '' ? Crypt::decryptString($this->attributes['ValidDate']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setSerialNumberAttribute($value)
    {
        $this->attributes['SerialNumber'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getSerialNumberAttribute($value)
    {
        try {
            return $this->attributes['SerialNumber'] !== null || $this->attributes['SerialNumber'] != '' ? Crypt::decryptString($this->attributes['SerialNumber']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setMotherNameAttribute($value)
    {
        $this->attributes['MotherName'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getMotherNameAttribute($value)
    {
        try {
            return $this->attributes['MotherName'] !== null || $this->attributes['MotherName'] != '' ? Crypt::decryptString($this->attributes['MotherName']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setFatherNameAttribute($value)
    {
        $this->attributes['FatherName'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getFatherNameAttribute($value)
    {
        try {
            return $this->attributes['FatherName'] !== null || $this->attributes['FatherName'] != '' ? Crypt::decryptString($this->attributes['FatherName']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setBirthPlaceAttribute($value)
    {
        $this->attributes['BirthPlace'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getBirthPlaceAttribute($value)
    {
        try {
            return $this->attributes['BirthPlace'] !== null || $this->attributes['BirthPlace'] != '' ? Crypt::decryptString($this->attributes['BirthPlace']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setCityIDAttribute($value)
    {
        $this->attributes['CityID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getCityIDAttribute($value)
    {
        try {
            return $this->attributes['CityID'] !== null || $this->attributes['CityID'] != '' ? (int) Crypt::decryptString($this->attributes['CityID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDistrictIDAttribute($value)
    {
        $this->attributes['DistrictID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDistrictIDAttribute($value)
    {
        try {
            return $this->attributes['DistrictID'] !== null || $this->attributes['DistrictID'] != '' ? (int) Crypt::decryptString($this->attributes['DistrictID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setNeighborhoodAttribute($value)
    {
        $this->attributes['Neighborhood'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getNeighborhoodAttribute($value)
    {
        try {
            return $this->attributes['Neighborhood'] !== null || $this->attributes['Neighborhood'] != '' ? Crypt::decryptString($this->attributes['Neighborhood']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setVillageAttribute($value)
    {
        $this->attributes['Village'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getVillageAttribute($value)
    {
        try {
            return $this->attributes['Village'] !== null || $this->attributes['Village'] != '' ? Crypt::decryptString($this->attributes['Village']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setCoverNoAttribute($value)
    {
        $this->attributes['CoverNo'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getCoverNoAttribute($value)
    {
        try {
            return $this->attributes['CoverNo'] !== null || $this->attributes['CoverNo'] != '' ? Crypt::decryptString($this->attributes['CoverNo']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setPageNoAttribute($value)
    {
        $this->attributes['PageNo'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getPageNoAttribute($value)
    {
        try {
            return $this->attributes['PageNo'] !== null || $this->attributes['PageNo'] != '' ? Crypt::decryptString($this->attributes['PageNo']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setRegisterNoAttribute($value)
    {
        $this->attributes['RegisterNo'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getRegisterNoAttribute($value)
    {
        try {
            return $this->attributes['RegisterNo'] !== null || $this->attributes['RegisterNo'] != '' ? Crypt::decryptString($this->attributes['RegisterNo']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDateOfIssueAttribute($value)
    {
        $this->attributes['DateOfIssue'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDateOfIssueAttribute($value)
    {
        try {
            return $this->attributes['DateOfIssue'] !== null || $this->attributes['DateOfIssue'] != '' ? Crypt::decryptString($this->attributes['DateOfIssue']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

}
