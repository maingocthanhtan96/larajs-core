<?php

namespace LaraJS\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        Log::info('Incoming Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'body' => $request->except(['password', 'password_confirmation']),
            'user_agent' => $request->userAgent(),
        ]);
        Log::info('Outgoing Response', [
            'status' => $response->status(),
            'body_snippet' => Str::limit($response->getContent(), 200),
        ]);
    }
}
