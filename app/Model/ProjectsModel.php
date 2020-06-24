<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectsModel extends Model
{
    protected $table = "Projects";
    protected $primaryKey = "id";
    protected $appends = [
        'Manager',
    ];

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];

    public function getManagerAttribute()
    {

        $manager = $this->hasOne(EmployeeModel::class,"Id","manager_id");

        if ($manager)
        {
            return $manager->first();
        }
        else
        {
            return null;
        }

    }



}
