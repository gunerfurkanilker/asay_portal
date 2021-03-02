<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExpenseDocumentModel extends Model
{
    protected $table = "ExpenseDocument";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];



}
