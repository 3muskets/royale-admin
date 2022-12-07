<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Helper;
use Auth;

class PasswordViewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function changePasswordView()
    {
        $userDetails = PasswordController::getuserdetail();

        return view("auth/changepassword")->with(['data' => $userDetails]);
    }
}
