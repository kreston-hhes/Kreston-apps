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
        $categories = $request->input('category', []);

        return back()->with('success', [
            'name' => $request->input('name'),
            'category' => is_array($categories) ? $categories : [$categories],
            'kota' => $request->input('kota'),
            'gender' => $request->input('gender'),
            'agree' => $request->has('agree') ? 'Yes' : 'No',
            'permissions' => $request->input('permissions', []),
        ]);
    }
}
