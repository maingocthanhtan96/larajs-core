<?php

namespace LaraJS\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LangMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($request->header('X-Accept-Language', config('app.locale')));

        return $next($request);
    }
}
