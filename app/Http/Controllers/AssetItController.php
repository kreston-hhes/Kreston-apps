<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee; 
use App\Models\Asset;  
use App\Models\AssetLoan;

class AssetItController extends Controller
{
    public function index()
    {
        return view('pages.it.asset', ['title' => 'Asset IT']);
    }
}
