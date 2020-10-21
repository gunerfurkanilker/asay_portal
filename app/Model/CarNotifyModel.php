<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CarNotifyModel extends Model
{
    protected $table = "CarNotify";

    public static function saveCarNotify($request)
    {
        $request->TicketNo = "TKT-ARC-10000";
        if (isset($request->CarNotifyID))
            $carNotify = CarNotifyModel::find($request->CarNotifyID);
        else
            $carNotify = new CarNotifyModel();

        $ticketNoExists = CarNotifyModel::where(['Active' => 1,'TicketNo' => explode("-",$request->TicketNo)[2]])->first();
        if ($ticketNoExists)
        {
            $request->TicketNo = self::ticketNoExistsCheck($request->TicketNo);
        }

        $carNotify->RequestedFrom = $request->RequestedFrom;
        $carNotify->TicketNo = explode("-",$request->TicketNo)[2];//Sondaki numarayı alıyoruz sadece
        $carNotify->TicketKind = $request->TicketKind;
        $carNotify->CarPlate = $request->CarPlate;
        $carNotify->CarRegion = $request->CarRegion;
        $carNotify->CarCity = $request->CarCity;
        $carNotify->CarDefect = $request->CarIssueKind;
        $carNotify->CarKM = $request->CarKM;
        $carNotify->MissingCategories = $request->MissingCategories ? implode(",",$request->MissingCategories)  : null;
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
                        'filename' => 'CarNotifyDoc_' . $carNotify->id . '.' . $request->File->getClientOriginalExtension()
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
            $res = $client->request("POST", 'http://portal.asay.com.tr/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());


            if ($responseBody->status == true) {
                $carNotify->File = $responseBody->data;
                $carNotify->save();
            }

        }

        if ($result)
        {
            return ['status' => true,'message' => 'İşlem Başarılı'];
        }
        else
            return ['status' => false,'message' => 'İşlem Başarısız'];


    }

    public static function ticketNoExistsCheck($ticketNo){
        $maxTicketNo = CarNotifyModel::max("TicketNo") + 1;

        while($maxTicketNo != $ticketNo)
        {
            $ticketNo++;
        }

        return $ticketNo;
    }

}
