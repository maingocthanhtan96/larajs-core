<?php

namespace LaraJS\Core\Controllers;

use Illuminate\Http\Response;

class LaraJSController extends BaseLaraJSController
{
    public function setLanguage($language): Response
    {
        $cookie = cookie('language', $language);

        return response('success')->cookie($cookie);
    }
}
