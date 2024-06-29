<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class AuthMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (!isset($_SESSION['user'])) {
            return $response->withRedirect('/login');
        }
        $response = $next($request, $response);
        return $response;
    }
}
