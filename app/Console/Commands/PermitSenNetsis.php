<?php

namespace App\Console\Commands;

use App\Model\CompanyModel;
use App\Model\EmployeeModel;
use App\Model\PermitModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PermitSenNetsis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permit:sendNetsis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'İzinlerin Netsise Aktarılması';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //job olarak çalışacak
        $permits = PermitModel::where(["netsis"=>0,"active"=>1,"ps_status"=>1])->whereDate('start_date', '<=', date("Y-m-d H:i:s"))->get();

        $wsdl    = 'http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?wsdl';

        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 900);
        ini_set('default_socket_timeout', 15);

        $options = array(
            'uri'               =>'http://schemas.xmlsoap.org/wsdl/soap/',
            'style'             =>SOAP_RPC,
            'use'               =>SOAP_ENCODED,
            'soap_version'      =>SOAP_1_1,
            'cache_wsdl'        =>WSDL_CACHE_NONE,
            'connection_timeout'=>15,
            'trace'             =>true,
            'encoding'          =>'UTF-8',
            'exceptions'        =>true,
            "location" => "http://netsis.asay.corp/CrmNetsisEntegrasyonServis/Service.svc?singleWsdl",
        );
        $soap = new \SoapClient($wsdl, $options);
        $izin["_Isyeri"]                = "YASAYVAD"; // İş Yeri ne olacak ?
        $izin["_SicilNo"]               = "10802";
        $izin["_BasTarih"]              = new Carbon("2020-11-29");
        $izin["_BitTarih"]              = new Carbon("2020-11-31");
        $izin["_IsGunuSayisi"]          = 1;
        $izin["_HaftaSonuTatilSayisi"]  = 0;
        $izin["_IzinTuru"]              = "I";
        $izin["_NedenKodu"]             = "";
        //$izinResponse = $soap->PersonelIzinGirisi($izin);

        foreach ($permits as $permit) {
            $employee = EmployeeModel::where(["Id"=>$permit->EmployeeID,"Active"=>1])->first();
            $company = CompanyModel::find($employee->EmployeePosition->CompanyID);
            $companyCode = $company->NetsisName;
            if($companyCode=="Asay_Elektronik"){
                $companyCode = "ASAYELEK";
            }
            elseif($companyCode=="Asay_Enerji"){
                $companyCode = "ASAYENER";
            }
            elseif($companyCode=="Asay_Iletisim"){
                $companyCode = "ASAYILET";
            }
            elseif($companyCode=="Asay_Vad_Otomasyon"){
                $companyCode = "YASAYVAD";
            }

            $izin["_Isyeri"]                = $companyCode; // İş Yeri ne olacak ?
            $izin["_SicilNo"]               = EmployeeModel::where('Id',$permit->EmployeeID)->first()->StaffID;
            $izin["_BasTarih"]              = new Carbon($permit->start_date);
            $izin["_BitTarih"]              = new Carbon($permit->start_date);
            $izin["_IsGunuSayisi"]          = $permit->used_day;
            $izin["_HaftaSonuTatilSayisi"]  = $permit->weekend;
            $izin["_IzinTuru"]              = "I";
            $izin["_NedenKodu"]             = "";

            try
            {
                $soap = new \SoapClient($wsdl, $options);
                $data = $soap->PersonelIzinGirisi($izin);
                $permit->netsis = 1;
                $permit->save();
                //TODO: netsis izin kaydı başarılı log yazılacak
                try
                {
                    $puantaj["_Isyeri"] = "YASAYVAD"; // İş Yeri ne olacak ?
                    $puantaj["_SicilNo"] = "10802";
                    $puantaj["Yil"] = "2020";
                    $puantaj["Ay"] = "11";
                    $puantaj["GunSayisi"] = "1";
                    $puantaj["PuantajTuru"] = "IZIN";
                    $dataPuantaj = $soap->PersonelPuantajKayit($puantaj);
                    $permit->puantaj = 1;
                    $permit->save();
                    //TODO: netsis puantaj kaydı başarılı log yazılacak
                }
                catch(Exception $e2)
                {
                    //TODO: netsis puantaj kaydı başarısız log yazılacak
                }
            }
            catch(Exception $e)
            {
                //TODO: netsis izin kaydı başarısız log yazılacak
            }
        }
    }
}
