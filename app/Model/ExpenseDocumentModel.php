<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseDocumentModel extends Model
{
    protected $table = "ExpenseDocument";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
