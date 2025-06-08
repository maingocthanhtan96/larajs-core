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
        $lang = $request->header('X-Accept-Language', config('app.locale'));

        App::setLocale($lang);

        return $next($request);
    }
}
