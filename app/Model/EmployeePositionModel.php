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
        $data['Cities'] = CityModel::where('Id',35)->get();
        $data['Districts'] = DistrictModel::where('CityId',35)->get();
        $data['Departments'] = DepartmentModel::all();
        $data['Titles'] = TitleModel::all();
        $data['Managers'] = EmployeeModel::all();
        $data['WorkingTypes'] = WorkingTypeModel::all();

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
                'EndDate' =>new Carbon($requestData['positionenddate'])
            ]);
            $salary->save();

            return $salary->fresh();
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


    public function getTitleAttribute(){
        $title = $this->hasOne(TitleModel::class,'Id','TitleID');
        return $title->first();
    }

    public function getCompanyAttribute(){
        $company = $this->hasOne(CompanyModel::class,'Id','CompanyID');
        return $company->first();
    }

 }
