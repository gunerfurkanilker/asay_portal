<?php

namespace App\Library;


class Bitrix
{
    public static function GetApi($command,$post=array())
    {
        $queryUrl = 'https://connect.asay.com.tr/rest/515/urks7c20xs5g2pq0/'.$command;
        $queryData= http_build_query($post);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER=> 0,
            CURLOPT_POST          => 1,
            CURLOPT_HEADER        => 0,
            CURLOPT_RETURNTRANSFER=> 1,
            CURLOPT_URL           => $queryUrl,
            CURLOPT_POSTFIELDS    => $queryData,
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);
        return $result;
    }

    public static function GetProjeApi($command,$post=array())
    {
        $queryUrl = 'https://connect.asay.com.tr/asay_api/proje.api.php?method='.$command;
        $queryData= http_build_query($post);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER=> 0,
            CURLOPT_POST          => 1,
            CURLOPT_HEADER        => 0,
            CURLOPT_RETURNTRANSFER=> 1,
            CURLOPT_URL           => $queryUrl,
            CURLOPT_POSTFIELDS    => $queryData,
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}
