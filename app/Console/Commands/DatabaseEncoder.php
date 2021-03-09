<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\Common\CurrencyController;
use App\Model\AgiModel;
use App\Model\BodyMeasurementModel;
use App\Model\CityModel;
use App\Model\CurrencyModel;
use App\Model\DistrictModel;
use App\Model\EmergencyFieldModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use App\Model\LowerBodyModel;
use App\Model\NationalityModel;
use App\Model\PaymentModel;
use App\Model\PayMethodModel;
use App\Model\PayPeriodModel;
use App\Model\ShoeSizeModel;
use App\Model\SocialSecurityInformationModel;
use App\Model\UpperBodyModel;
use App\Model\IdCardModel;
use http\Message\Body;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class DatabaseEncoder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:PortalEncoder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database Şifreleme';

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
        $all = IdCardModel::where("CityId","=","KOCAELİ")->get();
        foreach ($all as $item) {
            echo $item->Id."\n";
            if($item->CityID!==null){
                $city = CityModel::where(["Sym"=>$item->CityID])->first();
                if($city)
                    $item->CityID = Crypt::encryptString($city->Id);
            }
            if($item->DistrictID!==null && $city){
                $district = DistrictModel::where(["Sym"=>$item->DistrictID,"CityID"=>$city->Id])->first();
                if($district)
                    $item->DistrictID = Crypt::encryptString($district->Id);
            }
            $item->save();
        }
    }
}
