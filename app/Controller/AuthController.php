<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthController
{
    protected $container;
    protected $db;
    protected $jwt_secret = "your_secret_key"; // Pastikan untuk mengganti dengan kunci rahasia Anda

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
    }

    public function showLoginForm(Request $request, Response $response, array $args)
    {
        return $this->container->get('view')->render($response, 'login.php', $args);
    }

    public function login(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        $user = $this->db->get('users', [
            '[>]roles' => ['role_id' => 'id'],
        ], [
            'users.id',
            'users.username',
            'users.email',
            'users.password',
            'roles.role_name',
        ], [
            'users.username' => $username,
        ]);

        if ($user && password_verify($password, $user['password'])) {
            $token = $this->generateJWT($user);

            if ($user['role_name'] === 'admin') {
                $_SESSION['admin'] = $user;
                $_SESSION['admin_token'] = $token;
                return $response->withRedirect('/dashboardAdmin');
            } else {
                $_SESSION['user'] = $user;
                $_SESSION['user_token'] = $token;
                return $response->withRedirect('/user');
            }
        } else {
            return $response->withRedirect('/login');
        }
    }

    public function logout(Request $request, Response $response, array $args)
    {
        if (isset($_SESSION['admin'])) {
            unset($_SESSION['admin']);
            unset($_SESSION['admin_token']);
        }

        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
            unset($_SESSION['user_token']);
        }

        return $response->withRedirect('/login');
    }

    public function showRegisterForm(Request $request, Response $response, array $args)
    {
        return $this->container->get('view')->render($response, 'register.php', $args);
    }

    public function register(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Ambil role_id untuk role "user"
        $role = $this->db->get('roles', 'id', ['role_name' => 'user']);

        $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role_id' => $role,
        ]);

        return $response->withRedirect('/login');
    }

    public function showForgotPasswordForm(Request $request, Response $response, array $args)
    {
        return $this->container->get('view')->render($response, 'forgot-password.php', $args);
    }

    public function forgotPassword(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'];

        $user = $this->db->get('users', '*', ['email' => $email]);

        if ($user) {
            // Arahkan ke halaman edit-forgot-password dengan email sebagai query parameter
            return $response->withRedirect('/edit-forgot-password?email=' . urlencode($email));
        }

        return $response->withRedirect('/forgot-password');
    }

    public function showEditForgotPasswordForm(Request $request, Response $response, array $args)
    {
        $email = $request->getQueryParam('email');
        return $this->container->get('view')->render($response, 'edit-forgot-password.php', ['email' => $email]);
    }

    public function processEditForgotPassword(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'];
        $password = $data['password'];
        $retypePassword = $data['retype_password'];

        if ($password !== $retypePassword) {
            // Password dan retype password tidak cocok
            return $response->withRedirect('/edit-forgot-password?email=' . urlencode($email));
        }

        // Hash password baru
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password di database
        $this->db->update('users', ['password' => $hashedPassword], ['email' => $email]);

        return $response->withRedirect('/login');
    }

    protected function generateJWT($user)
    {
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'exp' => time() + (60 * 60), // Token berlaku selama 1 jam
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }
}
