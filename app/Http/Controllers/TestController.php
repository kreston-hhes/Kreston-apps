<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('pages.test-form', [
            'title' => 'Test Form',
        ]);
    }

public function store(Request $request)
    {
        return back()->with('success', [
            'name' => $request->name,
            'category' => $request->category,
        ]);
    }
}
