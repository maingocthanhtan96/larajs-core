<?php

namespace LaraJS\Core\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaraJSController extends BaseLaraJSController
{
    public function setLanguage($language): Response
    {
        $week = 10080; // a week
        $cookie = cookie('language', $language, $week);

        return response('success')->cookie($cookie);
    }

    public function logging(Request $request): JsonResponse
    {
        try {
            $logging = $request->get('logging', 2);
            $platform = match ($logging) {
                0 => 'frontend',
                1 => 'cms',
                2 => 'application',
            };
            \Log::channel($platform)->error($request->get('message'), $request->only('stack', 'info', 'screen'));

            return $this->jsonMessage(message: 'Store log success', showMessage: false);
        } catch (\Exception $e) {
            return $this->jsonError($e);
        }
    }
}
