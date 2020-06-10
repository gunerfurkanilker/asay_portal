<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseModel extends Model
{
    protected $table = "Expense";
    protected $primaryKey = "id";
    protected $appends = [
        'Project',
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
}
