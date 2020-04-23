<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeePositionModel extends Model
{
    protected $primaryKey = 'Id';
    protected $table = 'EmployeePosition';

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

 }
