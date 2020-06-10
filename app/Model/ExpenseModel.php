<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseModel extends Model
{
    protected $table = "Expense";
    protected $primaryKey = "id";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
