<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuthRedirectsController extends Controller
{
    /**
     * Every authenticated entry point funnels through here
     * (config/fortify.php `home`), so this decides where each role lands.
     */
    public function index(){
        if (Gate::allows('user-is-admin')) {
            return redirect()->route('AdminDashboard')->with('success', 'Welcome back!');
        }
        if (Gate::allows('user-is-an-applicant')) {
            return redirect()->route('applicant.dashboard')->with('success', 'Welcome back!');
        }

        // Matching neither gate previously returned null, rendering a blank
        // page. Anyone on an unrecognised role gets a real destination.
        return redirect()->route('home');
    }
}
