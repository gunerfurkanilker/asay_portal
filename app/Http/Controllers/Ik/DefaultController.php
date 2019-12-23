<?php

namespace App\Http\Controllers\Ik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DefaultController extends Controller
{
    public function index()
    {
        return view("ik.index");
    }
}
