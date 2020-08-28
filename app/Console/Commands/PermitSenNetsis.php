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
        $permits = PermitModel::where(["netsis"=>0,"active"=>1,"ps_status"=>1])->whereDate('created_date', '<=', date("Y-m-d H:i:s"))->get();

        foreach ($permits as $permit) {
            $employee = EmployeeModel::where(["Id"=>$permit->EmployeeID,"Active"=>1])->first();
            $company = CompanyModel::find($employee->EmployeePosition->CompanyID);

            if($company->Sym=="aSAY Elektronik"){
                $companyCode = "ASAYELEK";
            }
            elseif($company->Sym=="aSAY Energy"){
                $companyCode = "ASAYENER";
            }
            elseif($company->Sym=="aSAY Comm"){
                $companyCode = "ASAYILET";
            }
            elseif($company->Sym=="aSAY VAD"){
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
            try
            {
                $soap = new \SoapClient($wsdl, $options);
                $data = $soap->PersonelIzinGirisi($izin);
                $permit->netsis = 1;
                $permit->save();
                //TODO: log yazılacak
                /*return response([
                    'status' => true,
                    'message' => "Kayıt Başarılı",
                    'resultMessage' => $data->PersonelIzinGirisiResult->Sonuc."-".$data->PersonelIzinGirisiResult->Aciklama
                ], 200);*/
            }
            catch(Exception $e)
            {
                //TODO: log yazılacak
                /*return response([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 200);*/
            }
        }
    }
}
