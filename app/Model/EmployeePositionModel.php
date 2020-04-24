<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeePositionModel extends Model
{
    protected $primaryKey = 'Id';
    protected $table = 'EmployeePosition';
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = [
      'Title',
      'Company'
    ];

    public static function getPositionFields()
    {
        $data = [];
        $data['Companies'] = CompanyModel::all();
        $data['Cities'] = CityModel::all();
        $data['Districts'] = DistrictModel::all();
        $data['Departments'] = DepartmentModel::all();
        $data['Titles'] = TitleModel::all();
        $data['Managers'] = EmployeeModel::all();
        $data['WorkingTypes'] = WorkingTypeModel::all();

        return $data;

    }

    public static function addJobPosition($requestData)
    {
        /*$salary = self::create([
            'Pay' => $request['pay'],
            'PaymentID' => $request['paymentid'],
            'CurrencyID' => $request['currencyid'],
            'AdditionalPaymentTypeID' => $request['additionalpaymenttypeid'],
            'PayPeriodID' => $request['payperiod']
        ]);*/
    }

    public static function saveJobPosition($position,$requestData)
    {

        $position->EmployeeID = $requestData['employeeid'];
        $position->CompanyID = $requestData['companyid'];
        $position->CityID = $requestData['cityid'];
        $position->DistrictID = $requestData['districtid'];
        $position->DepartmentID = $requestData['departmentid'];
        $position->TitleID = $requestData['titleid'];
        $position->ManagerID = $requestData['managerid'];
        $position->WorkingTypeID = $requestData['workingtypeid'];
        $position->PositionStartDate = new Carbon($requestData['positionstartdate']);
        $position->PositionEndDate = new Carbon($requestData['positionenddate']);

        if ($position->save())
            return $position->fresh();
        else
            return false;

    }

    public function getTitleAttribute(){
        $title = $this->hasOne(TitleModel::class,'Id','TitleID');
        return $title->first();
    }

    public function getCompanyAttribute(){
        $company = $this->hasOne(CompanyModel::class,'Id','CompanyID');
        return $company->first();
    }

 }
