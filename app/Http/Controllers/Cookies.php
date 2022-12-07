<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Cookie;

class Cookies extends Controller
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

    public function setSidebar(Request $request) 
    {
        $minimized = $request->input('minimized');

        $content = '';

        if($minimized == 'y')
        {
            $content = 'sidebar-minimized brand-minimized';
        }

        Cookie::queue(Cookie::forever('sidebar', $content));
    }

}
