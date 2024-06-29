<?php

use App\Controller\AuthController;
use App\Controller\CartController;
use App\Controller\DashboardController;
use App\Controller\PaymentController;
use App\Controller\UserController;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $authMiddleware = function ($request, $response, $next) {
        if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
            return $response->withRedirect('/login');
        }
        return $next($request, $response);
    };

    // Home
    $app->get('/', function (Request $request, Response $response, array $args) use ($container) {
        return $this->get('view')->render($response, 'index.html', $args);
    });

    // Cart Routes
    $app->get('/cart', CartController::class . ':displayCart')->add($authMiddleware);
    $app->post('/cart/add', CartController::class . ':addToCart')->add($authMiddleware);
    $app->get('/cart/order', CartController::class . ':orderCart')->add($authMiddleware);
    $app->post('/cart/confirm', CartController::class . ':confirmOrder')->add($authMiddleware);
    $app->post('/cart/update', CartController::class . ':updateCart')->add($authMiddleware);
    $app->post('/cart/remove', CartController::class . ':removeFromCart')->add($authMiddleware);

    // Payment Routes
    $app->get('/payment', PaymentController::class . ':paymentPage')->add($authMiddleware);
    $app->get('/payment/status', PaymentController::class . ':paymentStatus')->add($authMiddleware);
    $app->post('/payment/process', PaymentController::class . ':processPayment')->add($authMiddleware);

    // User Routes
    $app->get('/user', UserController::class . ':userPage')->add($authMiddleware);

    // Authentication Routes
    $app->get('/logout', AuthController::class . ':logout');
    $app->get('/login', AuthController::class . ':showLoginForm');
    $app->post('/login', AuthController::class . ':login');
    $app->get('/register', AuthController::class . ':showRegisterForm');
    $app->post('/register', AuthController::class . ':register');
    $app->get('/forgot-password', AuthController::class . ':showForgotPasswordForm');
    $app->post('/forgot-password', AuthController::class . ':forgotPassword');
    $app->get('/edit-forgot-password', AuthController::class . ':showEditForgotPasswordForm');
    $app->post('/edit-forgot-password', AuthController::class . ':processEditForgotPassword');

    // Dashboard Admin Routes
    $app->get('/dashboardAdmin', DashboardController::class . ':dataUser')->add($authMiddleware);
    $app->get('/dashboardAdmin/edit-user/{id}', DashboardController::class . ':editUser')->add($authMiddleware);
    $app->post('/dashboardAdmin/proses-edit-user/{id}', DashboardController::class . ':processEditUser')->add($authMiddleware);
    $app->get('/dashboardAdmin/tambah-user', DashboardController::class . ':addUserForm')->add($authMiddleware);
    $app->post('/dashboardAdmin/proses-tambah-user', DashboardController::class . ':processAddUser')->add($authMiddleware);
    $app->get('/dashboardAdmin/delete-user/{id}', DashboardController::class . ':deleteUser')->add($authMiddleware);
    $app->get('/dashboardAdmin/data-barang', DashboardController::class . ':dataProduct')->add($authMiddleware);
    $app->get('/dashboardAdmin/tambah-barang', DashboardController::class . ':addProduct')->add($authMiddleware);
    $app->post('/dashboardAdmin/proses-tambah-barang', DashboardController::class . ':processAddProduct')->add($authMiddleware);
    $app->get('/dashboardAdmin/edit-barang/{id}', DashboardController::class . ':editProduct')->add($authMiddleware);
    $app->post('/dashboardAdmin/proses-edit-barang/{id}', DashboardController::class . ':processEditProduct')->add($authMiddleware);
    $app->get('/dashboardAdmin/delete-barang/{id}', DashboardController::class . ':deleteProduct')->add($authMiddleware);
    $app->get('/dashboardAdmin/order-detail', DashboardController::class . ':orderDetail')->add($authMiddleware);
    $app->post('/dashboardAdmin/confirm-order', DashboardController::class . ':confirmOrder')->add($authMiddleware);
    $app->post('/dashboardAdmin/cancel-order', DashboardController::class . ':cancelOrder')->add($authMiddleware);
    $app->get('/dashboardAdmin/order-details/{id}', DashboardController::class . ':getOrderDetails')->add($authMiddleware);

};
