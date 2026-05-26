<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetItController extends Controller
{
    public function index()
    {
        return view('pages.it.asset', [
            'title' => 'Asset IT',
        ]);
    }
}
