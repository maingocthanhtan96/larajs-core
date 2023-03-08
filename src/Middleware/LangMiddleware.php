<?php

namespace LaraJS\Core\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class LangMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cookie = $request->cookie('language', config('app.locale'));
        App::setLocale($cookie);

        return $next($request);
    }
}
