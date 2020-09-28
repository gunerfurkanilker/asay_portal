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

    public static function saveIDCard($request)
    {
        $employee = EmployeeModel::find($request->EmployeeID);
        if ($employee->IDCardID)
            $IDCard = self::find($employee->IDCardID);
        else
            $IDCard = null;


        if ($IDCard == null)
            $IDCard = new IdCardModel();

        if ($request->hasFile('IDCardPhoto')) {
            $file = file_get_contents($request->IDCardPhoto->path());
            $guzzleParams = [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'IDCardPhoto_' . $employee->Id . '.' . $request->IDCardPhoto->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'id_card'
                    ],
                    [
                        'name' => 'token',
                        'contents' => $request->token
                    ]
                ]
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://portal.asay.com.tr/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $employee->Photo = $responseBody->data;
                $employee->save();
            }
        }



            $IDCard->ValidDate = $request->ValidDate != null ? $request->ValidDate : null;
        $IDCard->NewIDCard = $request->NewIDCard ? 1 : 0;
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
        $IDCard->BirthPlace = $request->BirthPlace;
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
        $res = $client->request("POST", 'http://portal.asay.com.tr/api/disk/addFile', $guzzleParams);
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

    public function getCityAttribute()
    {
        $city = $this->hasOne(CityModel::class, "Id", "GenderID");
        return $city->where("Active", 1)->first();
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

}
