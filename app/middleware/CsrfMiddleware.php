<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $token = $request->input('_token');
        if (!Csrf::validate(is_string($token) ? $token : null)) {
            http_response_code(419);
            exit('CSRF token mismatch');
        }
    }
}
