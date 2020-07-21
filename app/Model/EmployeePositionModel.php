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
      'Company',
      'WorkingType',
      'Department',
      'City',
      'District',
      'Manager'
    ];

    public static function getPositionFields()
    {
        $data = [];
        $data['Companies'] = CompanyModel::all();
        $data['Cities'] = CityModel::all();
        $data['Districts'] = DistrictModel::where('CityId',35)->get();
        $data['Departments'] = DepartmentModel::all();
        $data['Titles'] = TitleModel::all();
        $data['Managers'] = EmployeeModel::all();
        $data['WorkingTypes'] = WorkingTypeModel::where('Active',1)->get();

        return $data;

    }

    public static function addJobPosition($requestData)
    {
        try{
            $salary = self::create([
                'EmployeeID' => $requestData['employeeid'],
                'CompanyID' => $requestData['companyid'],
                'CityID' => $requestData['cityid'],
                'DistrictID' => $requestData['districtid'],
                'DepartmentID' => $requestData['departmentid'],
                'TitleID' => $requestData['titleid'],
                'ManagerID' => $requestData['managerid'],
                'WorkingTypeID' => $requestData['workingtypeid'],
                'StartDate' => new Carbon($requestData['positionstartdate']),
                'EndDate' => new Carbon($requestData['positionenddate']),
                'Active' => $requestData['activeposition'] ? 1 : 0
            ]);

            if ($requestData['actualposition'])
            {
                $actualPosition = self::checkActualPositionExists($requestData['employeeid']);

                if ($actualPosition)
                {
                    $actualPosition->Active = 1 ;
                    $actualPosition->save();
                }

                $salary->Active = 2;
            }


            $salary->save();

            return self::checkActualPositionExists($requestData['employeeid']);
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function editJobPosition($position,$requestData)
    {

        $position->EmployeeID = $requestData['employeeid'];
        $position->CompanyID = $requestData['companyid'];
        $position->CityID = $requestData['cityid'];
        $position->DistrictID = $requestData['districtid'];
        $position->DepartmentID = $requestData['departmentid'];
        $position->TitleID = $requestData['titleid'];
        $position->ManagerID = $requestData['managerid'];
        $position->WorkingTypeID = $requestData['workingtypeid'];
        $position->StartDate = new Carbon($requestData['positionstartdate']);
        $position->EndDate = new Carbon($requestData['positionenddate']);
        $position->Active = $requestData['activeposition'] ? 1:0;

        if ($requestData['actualposition'])
        {
            $actualPosition = self::checkActualPositionExists($requestData['employeeid']);

            if ($actualPosition)
            {
                $actualPosition->Active = 1 ;
                $actualPosition->save();
            }

            $position->Active = 2;
        }

        if ($position->save())
            return $position->fresh();
        else
            return false;

    }

    public static function deleteJobPosition($id)
    {
        $position = EmployeePositionModel::find($id);
        try
        {
            $position->delete();
            return true;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

    }

    public static function checkActualPositionExists($employeeID)
    {
        $position = self::where("EmployeeID",$employeeID)->where("Active",2)->first();

        return $position ? $position : false;

    }


    public function getTitleAttribute(){
        $title = $this->hasOne(TitleModel::class,'Id','TitleID');
        return $title->first();
    }

    public function getCompanyAttribute(){
        $company = $this->hasOne(CompanyModel::class,'Id','CompanyID');
        return $company->first();
    }

    public function getWorkingTypeAttribute(){
        $workingType = $this->hasOne(WorkingTypeModel::class,'Id','WorkingTypeID');
        return $workingType->first();
    }

    public function getDepartmentAttribute(){
        $department = $this->hasOne(DepartmentModel::class,'Id','DepartmentID');
        return $department->first();
    }

    public function getCityAttribute(){
        $city = $this->hasOne(CityModel::class,'Id','CityID');
        return $city->first();
    }

    public function getDistrictAttribute(){
        $district = $this->hasOne(DistrictModel::class,'Id','DistrictID');
        return $district->first();
    }

    public function getManagerAttribute(){
        $manager = $this->hasOne(EmployeeModel::class,'Id','ManagerID');
        return $manager->first();
    }

 }
