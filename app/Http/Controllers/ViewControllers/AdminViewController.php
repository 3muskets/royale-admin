<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Helper;
use Log;

class AdminViewController extends Controller
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

        Helper::checkUAC('permissions.view_admin_list');

        return view('admin.admin');
    }

    public function new()
    {   
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.create_admin');

        $optionsStatus = AdminController::getOptionsStatus();

        $optionsAdminRoles = AdminController::getOptionsAdminRoles();


        return view('admin.admin-new')->with(['optionsStatus' => $optionsStatus
                                            ,'optionsAdminRoles' => $optionsAdminRoles
                                            ]);

    }

    public function details(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.view_admin_list');

        $id = $request->input('id');

        $data = AdminController::getAdmin($request);

        $optionsAdminRoles = AdminController::getOptionsAdminRoles();

        if($data == [])
            abort(404);
        
        $optionsStatus = AdminController::getOptionsStatus();

        return view('admin.admin-details')->with(['data' => $data,'optionsStatus' => $optionsStatus ,'optionsAdminRoles' => $optionsAdminRoles]);
    }


    public function newRoles()
    {   
        Helper::checkUAC('system.accounts.super.admin');

        return view('admin.admin-roles-new');
    }

    public function rolesDetails()
    {   
        Helper::checkUAC('system.accounts.super.admin');

        return view('admin.admin-roles-details');
    }

    public function editRoles()
    {   
        Helper::checkUAC('system.accounts.super.admin');

        return view('admin.admin-roles-edit');
    }


    // ajax
    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.view_admin_list');

        $data = AdminController::getList($request);

        return $data;
    }

    public function update(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.edit_admin_list');


        $data = AdminController::update($request);

        return $data;
    }

    public function create(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.create_admin');

        $data = AdminController::create($request);

        return $data;
    }

    public function changePassword(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.edit_admin_list');

        $data = AdminController::changePassword($request);

        return $data;
    }

    public function checkUser(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.edit_admin_list');

        $data = AdminController::checkUser($request);

        return $data;
    }


    public function createRoles(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = AdminController::createRoles($request);

        return $data;
    }

    public function getRolesList(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = AdminController::getRolesList($request);

        return $data;
    }

    public function deleteRole(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = AdminController::deleteRole($request);

        return $data;
    }

    public function getRolesPermission(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = AdminController::getRolesPermission($request);

        return $data;
    }

    public function editRolesPermission(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = AdminController::editRolesPermission($request);

        return $data;
    }

}
