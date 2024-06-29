<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UserController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function userPage(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $search = $request->getParam('search', '');
        $page = (int) $request->getParam('page', 1);
        $limit = 8;
        $offset = ($page - 1) * $limit;

        $totalProducts = $db->count('products', [
            '[>]categories' => ['category_id' => 'category_id'],
        ], [
            'products.product_id',
        ], [
            'products.name[~]' => "%$search%",
        ]);

        $products = $db->select('products', [
            '[>]categories' => ['category_id' => 'category_id'],
        ], [
            'products.product_id',
            'products.name',
            'products.description',
            'products.price',
            'products.image',
            'categories.name(category_name)',
            'products.category_id',
        ], [
            'products.name[~]' => "%$search%",
            'LIMIT' => [$offset, $limit],
        ]);

        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . '://' . $uri->getHost();
        if ($uri->getPort()) {
            $baseUrl .= ':' . $uri->getPort();
        }

        $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Guest';

        $args['products'] = $products;
        $args['base_url'] = $baseUrl;
        $args['username'] = $username;
        $args['total_pages'] = ceil($totalProducts / $limit);
        $args['current_page'] = $page;
        $args['search'] = $search;

        return $this->container->get('view')->render($response, 'user.php', $args);
    }
}
