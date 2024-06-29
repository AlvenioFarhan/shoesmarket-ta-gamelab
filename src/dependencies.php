<?php

use Medoo\Medoo;
use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // Start session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Registrasi Twig
    $container['view'] = function ($container) {
        $view = new \Slim\Views\Twig('../templates', [
            'cache' => false,
        ]);

        // Instantiate and add Slim specific extension
        $router = $container->get('router');
        $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
        $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
        return $view;
    };

    // Database
    $container['db'] = function ($c) {
        return new Medoo([
            'database_type' => 'mysql',
            'server' => $_ENV['DATABASE_HOST'],
            'database_name' => $_ENV['DATABASE_NAME'],
            'username' => $_ENV['DATABASE_USER'],
            'password' => $_ENV['DATABASE_PASS'],
        ]);
    };

    //AuthController
    $container[\App\Controller\AuthController::class] = function ($container) {
        return new \App\Controller\AuthController($container);
    };

    // Registrasi controller
    $container[\App\Controller\DashboardController::class] = function ($container) {
        return new \App\Controller\DashboardController($container);
    };

    // Middleware Auth registration
    $container['authMiddleware'] = function ($container) {
        return function ($request, $response, $next) {
            if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
                return $response->withRedirect('/login');
            }
            $response = $next($request, $response);
            return $response;
        };
    };
};
