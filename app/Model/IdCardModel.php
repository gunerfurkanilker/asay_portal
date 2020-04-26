<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class IdCardModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'IDCard';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'Gender',
        'City',
        'District',
        'Nationality'
    ];

    public static function saveIDCard($request, $IDCardID)
    {
        $IDCard = self::find($IDCardID);

        if ($IDCard != null) {

            $IDCard->NationalityID = $request['nationality'];
            $IDCard->TCNo = $request['tcno'];
            $IDCard->FirstName = $request['firstname'];
            $IDCard->LastName = $request['lastname'];
            $IDCard->BirthDate = new Carbon($request['birthdate']);
            $IDCard->GenderID = $request['gender'];
            $IDCard->SerialNumber = $request['idcardserialno'];
            $IDCard->LastEffectiveDate = new Carbon($request['lasteffective']);
            $IDCard->MotherName = $request['mothername'];
            $IDCard->FatherName = $request['fathername'];
            $IDCard->BirthPlace = $request['birthplace'];
            $IDCard->CityID = $request['city'];
            $IDCard->DistrictID = $request['district'];
            $IDCard->Neighborhood = $request['neighborhood'];
            $IDCard->Village = $request['village'];
            $IDCard->CoverNo = $request['coverno'];
            $IDCard->PageNo = $request['pageno'];
            $IDCard->RegisterNo = $request['registerno'];
           // $IDCard->DateOfIssue = new Carbon($request['dateofissue']);


            $IDCard->save();

            return $IDCard->fresh();
        }
        else
            return false;
    }

    public static function addIDCard($request,$employee)
    {

        $IDCard = self::create([
            'NationalityID' => $request['nationality'],
            'TCNo' => $request['tcno'],
            'FirstName' => $request['firstname'],
            'LastName' => $request['lastname'],
            'BirthDate' => new Carbon($request['birthdate']),
            'GenderID' => $request['gender'],
            'SerialNumber' => $request['idcardserialno'],
            'LastEffectiveDate' => new Carbon($request['lasteffective']),
            'MotherName' => $request['mothername'],
            'FatherName' => $request['fathername'],
            'BirthPlace' => $request['birthplace'],
            'CityID' => $request['city'],
            'DistrictID' => $request['district'],
            'Neighborhood' => $request['neighborhood'],
            'Village' => $request['village'],
            'CoverNo' => $request['coverno'],
            'PageNo' => $request['pageno'],
            'RegisterNo' => $request['registerno'],
           // 'DateOfIssue' => new Carbon($request['dateofissue'])

        ]);

        if ($IDCard != null)
        {
            $employee->IDCardID = $IDCard->Id;
            $employee->save();
            return $IDCard;
        }

        else
            return false;
    }

    public static function getIDCardFields()
    {
        $data = [];
        $data['Nationalities'] = NationalityModel::all();
        $data['Genders'] = GenderModel::all();
        $data['Cities'] = CityModel::all();
        $data['Districts'] = DistrictModel::where('CityID',35)->get();

        return $data;
    }

    public function getGenderAttribute()
    {
        $gender = $this->hasOne(GenderModel::class,"Id","GenderID");
        return $gender->where("Active",1)->first();
    }

    public function getCityAttribute()
    {
        $city = $this->hasOne(CityModel::class,"Id","GenderID");
        return $city->where("Active",1)->first();
    }

    public function getDistrictAttribute()
    {
        $district = $this->hasOne(DistrictModel::class,"Id","GenderID");
        return $district->where("Active",1)->first();
    }

    public function getNationalityAttribute()
    {
        $nationality = $this->hasOne(NationalityModel::class,"Id","NationalityID");
        return $nationality->where("Active",1)->first();
    }

}
