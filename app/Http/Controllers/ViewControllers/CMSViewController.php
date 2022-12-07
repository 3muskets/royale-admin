<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CMSController;

class CMSViewController  extends Controller
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
    
    public function index()
    {
        Helper::checkUAC('system.accounts.admin');

        $optionsStatus = CMSController::getOptionStatus();

        return view('cms.main-banner')->with(['optionsStatus' => $optionsStatus]);
    }


    public function indexAnn()
    {
        Helper::checkUAC('system.accounts.admin');

        $optionsStatus = CMSController::getOptionStatus();

        return view('cms.announcement')->with(['optionsStatus' => $optionsStatus]);
    }



    public function indexPopup()
    {
        Helper::checkUAC('system.accounts.admin');

        $optionsStatus = CMSController::getOptionStatus();

        return view('cms.pop-up')->with(['optionsStatus' => $optionsStatus]);
    }


    public function createBanner(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::createBanner($request);

        return $data;
    }

    public function createAnnouncement(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::createAnnouncement($request);

        return $data;
    }


    public function getMainBannerList(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::getMainBannerList($request);

        return $data;
    }
    
    public function getAnnouncementList(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::getAnnouncementList($request);

        return $data;
    }

    public function getPopUpDetail(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::getPopUpDetail($request);

        return $data;        
    }

    public function updateBanner(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::updateBanner($request);

        return $data;
    }


    public function updateAnnouncement(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::updateAnnouncement($request);

        return $data;
    }

    public function updatePopup(Request $request)
    {   
        Helper::checkUAC('system.accounts.admin');

        $data = CMSController::updatePopup($request);

        return $data;
    }



}
