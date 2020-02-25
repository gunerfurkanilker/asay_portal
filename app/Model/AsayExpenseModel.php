<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AsayExpenseModel extends Model
{
    protected $table = "asay_expense";
    protected $primaryKey = "ID";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
