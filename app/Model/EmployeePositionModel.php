<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeePositionModel extends Model
{
    protected $primaryKey = 'Id';
    protected $table = 'EmployeePosition';
    public $timestamps = false;
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

    public static function saveJobPosition($employee,$requestData)
    {
        $employee->CompanyID = $requestData['companyid'];
        $employee->CityID = $requestData['cityid'];
        $employee->DistrictID = $requestData['districtid'];
        $employee->DepartmentID = $requestData['departmentid'];
        $employee->TitleID = $requestData['titleid'];
        $employee->ManagerID = $requestData['managerid'];
        $employee->WorkingTypeID = $requestData['workingtypeid'];
        $employee->PositionStartDate = new Carbon($requestData['positionstartdate']);
        $employee->PositionEndDate = new Carbon($requestData['positionenddate']);

        if ($employee->save())
            return $employee->fresh();
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
