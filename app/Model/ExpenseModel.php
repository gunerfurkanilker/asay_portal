<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseModel extends Model
{
    protected $table = "Expense";
    protected $primaryKey = "id";
    protected $appends = [
        'Project',
        'Category',
        'EmployeeManager',
        'Employee'
    ];

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];

    public function getProjectAttribute()
    {

        $project = $this->hasOne(ProjectsModel::class,"id","project_id");
        if ($project)
        {
            return $project->first();
        }
        else
        {
            return null;
        }

    }

    public function getCategoryAttribute()
    {

        $category = $this->hasOne(ProjectCategoriesModel::class,"id","category_id");
        if ($category)
        {
            return $category->first();
        }
        else
        {
            return null;
        }

    }

    public function getEmployeeManagerAttribute()
    {

        $employeePosition = $this->hasOne(EmployeePositionModel::class,"EmployeeID","EmployeeID");
        if ($employeePosition)
        {
            $employeePosition = $employeePosition->where(['Active' => 2])->first();
            $employee = EmployeeModel::find($employeePosition->ManagerID);
            return $employee;
        }
        else
        {
            return null;
        }

    }

    public function getEmployeeAttribute()
    {

        $employee = $this->hasOne(EmployeeModel::class,"Id","EmployeeID");
        if ($employee)
        {
            return $employee->first();
        }
        else
        {
            return null;
        }

    }

}
