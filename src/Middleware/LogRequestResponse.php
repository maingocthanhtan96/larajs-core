<?php

namespace LaraJS\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        \Log::info('Request', [
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'content' => $request->getContent(),
        ]);
        \Log::info('Response', [
            'headers' => $response->headers,
            'content' => $response->getContent(),
        ]);
    }
}
