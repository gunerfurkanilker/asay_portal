<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdvancePaymentModel extends Model
{
    protected $table = "AdvancePayment";
    protected $primaryKey = "id";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [
        'Category'
    ];

    public function getCategoryAttribute()
    {

        $category = $this->hasOne(ProjectCategoriesModel::class,"id","CategoryId");
        if ($category)
        {
            return $category->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }
}
