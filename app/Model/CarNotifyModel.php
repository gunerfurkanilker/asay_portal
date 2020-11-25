<?php

namespace App\Model;

use App\Library\Asay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CarNotifyModel extends Model
{
    protected $table = "CarNotify";
    protected $appends = [
        'NotifyKind',
        'Region',
        'City',
        'IssueKind',
        'Car'
    ];

    public static function saveCarNotify($request)
    {

        if (isset($request->CarNotifyID))
            $carNotify = CarNotifyModel::find($request->CarNotifyID);
        else
            $carNotify = new CarNotifyModel();

        $carNotify->RequestedFrom = $request->RequestedFrom;
        $carNotify->TicketKind = $request->TicketKind;
        $carNotify->CarPlate = $request->CarPlate;
        $carNotify->CarRegion = $request->CarRegion;
        $carNotify->CarCity = $request->CarCity;
        $carNotify->CarIssueKind = $request->CarIssueKind;
        $carNotify->CarKM = $request->CarKM;
        $carNotify->MissingCategories = $request->MissingCategories;
        $carNotify->Subject = $request->Subject;
        $carNotify->Description = $request->Description;
        $result =$carNotify->save();

        if ($result && $request->hasFile('File')) {
            $file = file_get_contents($request->File->path());
            $guzzleParams = [

                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'Arac_Bildirim_Dosya_Eki_' . $carNotify->id . '.' . $request->File->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'carNotify'
                    ],
                    [
                        'name' => 'token',
                        'contents' => $request->token
                    ]

                ],

            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());


            if ($responseBody->status == true) {
                $carNotify->File = $responseBody->data;
                $result = $carNotify->save();
            }

        }


        if ($result)
        {
            $carNotify->fresh();
            NotificationsModel::saveNotification($carNotify->RequestedFrom,12,$carNotify->id,"Araç Bildirim",$request->CarPlate." plakalı araç için oluşturmuş olduğunuz destek kaydı sistemimize kaydedilmiştir","");
            $file = null;
            if ($request->hasFile('File'))
            {
                $file = self::getFileOfNotify($carNotify,$request->token);
            }
            $missingCategories = $carNotify->MissingCategories ? explode(",",$carNotify->MissingCategories) : [];
            $carNotify->MissingCategories = $missingCategories;
            $employee = EmployeeModel::find($request->RequestedFrom);
            $mailData = ['employee' => $employee, 'ticket' => $carNotify];
            $mailTable = view('mails.vehicle-notify', $mailData);

            $mailTo = "ilker.guner@asay.com.tr";

            /*switch ($employee->EmployeePosition->Organization->id)
            {
                case 4:
                    if ($employee->EmployeePosition->RegionID == 1) // Bursa ise
                        if($employee->EmployeePosition->City->Id == 41)
                            $mailTo = "aracbildirim.kocaeli@ms.asay.com.tr";
                        else
                            $mailTo = "aracbildirim.bursa@ms.asay.com.tr";
                    else if ($employee->EmployeePosition->RegionID == 2) // Asya ise
                        $mailTo = "aracbildirim.asya@ms.asay.com.tr";
                    else
                        $mailTo = "aracbildirim.avrupa@ms.asay.com.tr";
                    break;
                default:
                    $mailTo = "aracfilo@asay.com.tr";
                    break;
            }*/


            if ($file)
                Asay::sendMail("ilker.guner@asay.com.tr", $employee->JobEmail, "Araç Bildirim", $mailTable, "aSAY Group",$file->FilePath, $file->FileName, $file->MimeType);
            else
                Asay::sendMail("ilker.guner@asay.com.tr", $employee->JobEmail, "Araç Bildirim", $mailTable, "aSAY Group");

            return ['status' => true,'message' => 'İşlem Başarılı'];
        }
        else
            return ['status' => false,'message' => 'İşlem Başarısız'];


    }


    public static function getFileOfNotify($carNotify,$token)
    {
        $carNotify = CarNotifyModel::where(['id' => $carNotify->id, 'Active' => 1])->first();

        if ($carNotify->File) {
            $guzzleParams = [
                'query' => [
                    'token' => $token,
                    'fileId' => $carNotify->File
                ],
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("GET", 'http://'.\request()->getHttpHost().'/rest/api/disk/getFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $data = new \stdClass();
                $filePath = Storage::disk("connect")->path($responseBody->file->subdir . '/' . $responseBody->file->filename);;
                $fileName = $responseBody->file->original_name;
                $mimeType = $responseBody->file->content_type;
                $data->FilePath = $filePath;
                $data->FileName = $fileName;
                $data->MimeType = $mimeType;
                return $data;
            } else
                return false;
        }
    }







    public static function ticketNoExistsCheck($ticketNo){

        while(CarNotifyModel::max("TicketNo") >= $ticketNo)
        {
            ++$ticketNo;
        }

        return $ticketNo;
    }

    public function getNotifyKindAttribute()
    {

        $notifyKind = $this->hasOne(CarNotifyKindModel::class, "id", "TicketKind");
        if ($notifyKind) {
            return $notifyKind->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getRegionAttribute()
    {

        $carRegion = $this->hasOne(RegionModel::class, "id", "CarRegion");
        if ($carRegion) {
            return $carRegion->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getCityAttribute()
    {

        $carCity = $this->hasOne(CityModel::class, "Id", "CarCity");
        if ($carCity) {
            return $carCity->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getIssueKindAttribute()
    {

        $issueKind = $this->hasOne(CarNotifyIssueKindModel::class, "id", "CarIssueKind");
        if ($issueKind) {
            return $issueKind->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getCarAttribute()
    {

        $car = $this->hasOne(CarModel::class, "Plate", "CarPlate");
        if ($car) {
            return $car->where("Active", 1)->first();
        } else {
            return "";
        }

    }


}
