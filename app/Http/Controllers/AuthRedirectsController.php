<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuthRedirectsController extends Controller
{
    public function index(){
        if (Gate::allows('user-is-admin')) {
            return redirect()->route('AdminDashboard')->with('success', 'Welcome back!');
        } 
        if (Gate::allows('user-is-an-applicant')) {
            return redirect()->route('careers')->with('success', 'Welcome back!');
        } 
    }
}
