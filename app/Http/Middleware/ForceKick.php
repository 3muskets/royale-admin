<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Log;
use Auth;
use Session;
use Illuminate\Support\Facades\DB;

class ForceKick
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    
    public function handle($request, Closure $next)
    {
        if(Auth::check())
        {
            $userId = Auth::id();

            $db = DB::select("
                    SELECT login_token
                    FROM admin
                    WHERE id = ?
                ", [
                     $userId
                ]);
            
            $dbToken = $db[0]->login_token;
            $sessionToken = \Session::get('login_token');
            
            if($sessionToken != $dbToken)
            {
                Auth::logout();

                if($request->ajax())
                {
                    if ($dbToken) 
                    {
                        //multiple sign in
                        abort(440);
                    }
                    else
                    {
                        // status inactive
                        abort(441);
                    }
                    
                }
                else
                {
                    if ($dbToken) 
                    {
                        //multiple sign in
                        return redirect('/?k=1');
                    }
                    else
                    {
                        // status inactive
                        return redirect('/?k=2');
                    }
                }
            }
        }

        return $next($request);
    }
}
