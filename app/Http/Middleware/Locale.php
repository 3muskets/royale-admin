<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Cookie;

class Locale
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
        $locale = Cookie::get('app_locale');

        if($locale) 
        {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
