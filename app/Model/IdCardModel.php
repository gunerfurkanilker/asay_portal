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
        'Nationality',
        'ObjectFile'
    ];

    public static function saveIDCard($request, $IDCardID)
    {
        $IDCard = self::find($IDCardID);

        if ($IDCard != null) {


            if ($request->hasFile('IDCardPhoto'))
            {
                $file = file_get_contents($request->IDCardPhoto->path());
                $guzzleParams = [

                    'multipart' =>[
                        [
                            'name' => 'token',
                            'contents' => $request->token
                        ],
                        [
                            'name' => 'ObjectType',
                            'contents' => 7 // Kimlik
                        ],
                        [
                            'name' => 'ObjectTypeName',
                            'contents' =>  'IDCard'
                        ],
                        [
                            'name' => 'ObjectId',
                            'contents' => $IDCard->Id
                        ],
                        [
                            'name' => 'file',
                            'contents' => $file,
                            'filename' => 'kimlik_' . $IDCard->Id . '.' . $request->IDCardPhoto->getClientOriginalExtension()
                        ],

                    ],
                ];

                $client = new \GuzzleHttp\Client();
                $res    = $client->request("POST",'http://lifi.asay.com.tr/connectUpload',$guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == false)
                    return false;

            }



            $IDCard->ValidDate              = $request->ValidDate != null ? $request->ValidDate : null;
            $IDCard->NewIDCard              = $request->NewIDCard ? 1 : 0;
            $IDCard->NationalityID          = $request->NationalityID;
            $IDCard->TCNo                   = $request->TCNo;
            $IDCard->FirstName              = $request->FirstName;
            $IDCard->LastName               = $request->LastName;
            $IDCard->BirthDate              = $request->BirthDate;
            $IDCard->GenderID               = $request->GenderID;
            $IDCard->SerialNumber           = $request->SerialNumber;
            $IDCard->DateOfIssue            = $request->DateOfIssue != null ? $request->DateOfIssue : null ;
            $IDCard->MotherName             = $request->MotherName;
            $IDCard->FatherName             = $request->FatherName;
            $IDCard->BirthPlace             = $request->BirthPlace;
            $IDCard->CityID                 = $request->CityID != null ? $request->CityID : null;
            $IDCard->DistrictID             = $request->DistrictID != null ? $request->DistrictID : null;
            $IDCard->Neighborhood           = $request->Neighborhood != null ? $request->Neighborhood : '';
            $IDCard->Village                = $request->Village != null ? $request->Village : '';
            $IDCard->CoverNo                = $request->CoverNo != null ? $request->CoverNo : '';
            $IDCard->PageNo                 = $request->PageNo != null ? $request->PageNo : '';
            $IDCard->RegisterNo             = $request->RegisterNo != null ? $request-> RegisterNo : '';

            $IDCard->save();

            return $IDCard->fresh();
        }
        else
            return false;
    }

    public static function addIDCard($request,$employee)
    {

        $photoLink = null;

        $IDCard = self::create([
            'NewIDCard'             => $request->NewIDCard ? 1 : 0,
            'NationalityID'         => $request->NationalityID,
            'TCNo'                  => $request->TCNo,
            'FirstName'             => $request->FirstName,
            'LastName'              => $request->LastName,
            'BirthDate'             => $request->BirthDate,
            'GenderID'              => $request->GenderID,
            'SerialNumber'          => $request->SerialNumber,
            'ValidDate'             => $request->ValidDate != null ? $request->ValidDate : null,
            'DateOfIssue'           => $request->DateOfIssue != null ? $request->DateOfIssue : null,
            'MotherName'            => $request->MotherName,
            'FatherName'            => $request->FatherName,
            'BirthPlace'            => $request->BirthPlace,
            'CityID'                => $request->CityID != null ? $request->CityID : null,
            'DistrictID'            => $request->DistrictID != null ? $request->DistrictID : null,
            'Neighborhood'          => $request->Neighborhood != null ? $request->Neighborhood : '',
            'Village'               => $request->Village != null ? $request->Village : '',
            'CoverNo'               => $request->CoverNo != null ? $request->CoverNo : '',
            'PageNo'                => $request->PageNo != null ? $request->PageNo : '',
            'RegisterNo'            => $request->RegisterNo != null ? $request->RegisterNo : '',
        ]);



        if ($IDCard != null)
        {

            if ($request->hasFile('IDCardPhoto'))
            {
                $file = file_get_contents($request->IDCardPhoto->path());
                $guzzleParams = [

                    'multipart' =>[
                        [
                            'name' => 'token',
                            'contents' => $request->token
                        ],
                        [
                            'name' => 'ObjectType',
                            'contents' => 7 // Harcama Masraf
                        ],
                        [
                            'name' => 'ObjectTypeName',
                            'contents' =>  'IDCard'
                        ],
                        [
                            'name' => 'ObjectId',
                            'contents' => $IDCard->Id
                        ],
                        [
                            'name' => 'file',
                            'contents' => $file,
                            'filename' => 'kimlik_' . $IDCard->Id . '.' . $request->IDCardPhoto->getClientOriginalExtension()
                        ],

                    ],
                ];

                $client = new \GuzzleHttp\Client();
                $res    = $client->request("POST",'http://lifi.asay.com.tr/connectUpload',$guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == false)
                    return false;
            }


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
        $data['Districts'] = DistrictModel::all();

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

    public function getObjectFileAttribute(){
        $objectFile = $this->hasOne(ObjectFileModel::class,'ObjectId','Id');
        return $objectFile->where(['Active' => 1, 'ObjectType' => 7])->first();//Kimlik Fotoğrafı
    }

}
