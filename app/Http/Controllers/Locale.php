<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Cookie;

class Locale extends Controller
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

    public function setLocale(Request $request) 
    {
        $validLocale = array(
                    'en'
                    ,'zh-cn'
                );

        $locale = $request->input('locale');

        if(in_array($locale,$validLocale))
        {
            Cookie::queue(Cookie::forever('app_locale', $locale));
        }

        return back();
    }


}
