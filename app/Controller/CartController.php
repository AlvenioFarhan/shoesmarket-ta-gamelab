<?php

namespace App\Controller;

use PDOException;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CartController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function displayCart(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $userId = $_SESSION['user']['id'];

        $cartItems = $db->select('cart', [
            '[>]products' => ['product_id' => 'product_id'],
        ], [
            'cart.cart_id',
            'products.name',
            'products.price',
            'cart.quantity',
            'products.image',
        ], [
            'cart.user_id' => $userId,
        ]);

        $totalPrice = array_reduce($cartItems, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        $args['cart_items'] = $cartItems;
        $args['total_price'] = $totalPrice;

        return $this->container->get('view')->render($response, 'cart.php', $args);
    }

    public function addToCart(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $data = $request->getParsedBody();
        $userId = $_SESSION['user']['id'];

        try {
            $db->insert('cart', [
                'user_id' => $userId,
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'],
            ]);
        } catch (PDOException $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }

        return $response->withRedirect('/cart');
    }

    public function orderCart(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $userId = $_SESSION['user']['id'];

        $cartItems = $db->select('cart', '*', ['user_id' => $userId]);

        if (!empty($cartItems)) {
            $db->insert('orders', [
                'user_id' => $userId,
                'total' => array_reduce($cartItems, function ($sum, $item) use ($db) {
                    $product = $db->get('products', '*', ['product_id' => $item['product_id']]);
                    return $sum + ($product['price'] * $item['quantity']);
                }, 0),
                'status' => 'Pending',
            ]);

            $orderId = $db->id();

            foreach ($cartItems as $item) {
                $product = $db->get('products', '*', ['product_id' => $item['product_id']]);
                $db->insert('order_details', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                ]);

                // Kurangi stok produk
                $db->update('products', [
                    'stock_quantity[-]' => $item['quantity'],
                ], [
                    'product_id' => $item['product_id'],
                ]);
            }

            $db->delete('cart', ['user_id' => $userId]);

            $orderItems = $db->select('order_details', [
                '[>]products' => ['product_id' => 'product_id'],
            ], [
                'products.name',
                'order_details.quantity',
                'order_details.price',
            ], [
                'order_id' => $orderId,
            ]);

            $totalPrice = array_reduce($orderItems, function ($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);

            $args['order_items'] = $orderItems;
            $args['total_price'] = $totalPrice;
            $args['order_id'] = $orderId;

            return $this->container->get('view')->render($response, 'order.php', $args);
        } else {
            return $response->withRedirect('/cart');
        }
    }

    public function confirmOrder(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody();
        $orderId = $data['order_id'];

        $db = $this->container->get('db');
        // Update order status to confirmed
        // $db->update('orders', ['status' => 'Confirmed'], ['order_id' => $orderId]);

        return $response->withRedirect('/payment/status');
    }

    public function updateCart(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $data = $request->getParsedBody();
        $userId = $_SESSION['user']['id'];
        $cartId = $data['cart_id'];
        $quantity = $data['quantity'];

        try {
            $db->update('cart', ['quantity' => $quantity], ['AND' => ['user_id' => $userId, 'cart_id' => $cartId]]);
        } catch (PDOException $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }

        return $response->withRedirect('/cart');
    }

    public function removeFromCart(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $data = $request->getParsedBody();
        $userId = $_SESSION['user']['id'];
        $cartId = $data['cart_id'];

        try {
            $db->delete('cart', ['AND' => ['user_id' => $userId, 'cart_id' => $cartId]]);
        } catch (PDOException $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }

        return $response->withRedirect('/cart');
    }
}
