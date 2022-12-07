<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Session;
use Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    protected function credentials(Request $request)
    {
        return ['username'=>$request->{$this->username()},'password'=>$request->password,'status'=>'a','up1_inactive'=>null,'up2_inactive'=>null];
    }

    protected function authenticated(Request $request, $user)
    {
        $userId = $user->id;
        $loginToken = \Session::getId();

        \Session::put('login_token',$loginToken);

        DB::UPDATE("
            UPDATE admin
            SET login_token = ?
            WHERE id = ?
            ", [
                 $loginToken
                ,$userId
            ]);

        return redirect('/home');
    }


    public function logout(Request $request) 
    {
        $user_id = Auth::id();
        
        Auth::logout();

        DB::UPDATE("
            UPDATE admin
            set login_token = NULL
            WHERE id = ?
        ", [
            $user_id
        ]);

        return redirect('/');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        //prepare redirect path
        $redirectToLogin = '/login';

        $username = $request->input('username');

        //check not exists
        $data = DB::SELECT("
                SELECT status,up1_inactive,up2_inactive
                FROM admin 
                where username = ?
            ", [
                $username
            ]);

        Log::Debug($data);

        //check is closed      
        if($data)
        {
            if ($data[0]->status == "i" || $data[0]->up1_inactive == "1"  || $data[0]->up2_inactive == "1") 
            {
                return redirect()->to($redirectToLogin)
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => __('auth.inactive'),
                ]); 
            }
            else
            {
                return redirect()->to($redirectToLogin)
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => __('auth.failed'),
                ]);
            }
            
        }
        else
        {
            return redirect()->to($redirectToLogin)
            ->withInput($request->only($this->username()))
            ->withErrors([
                $this->username() => __('auth.failed'),
            ]);
        }
    }

}
