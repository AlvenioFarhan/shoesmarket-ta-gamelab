<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class PaymentController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function paymentPage(Request $request, Response $response, array $args)
    {
        return $this->container->get('view')->render($response, 'payment.php', $args);
    }

    public function paymentStatus(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $userId = $_SESSION['user']['id'];

        $orders = $db->select('orders', '*', ['user_id' => $userId]);

        $args['orders'] = $orders;

        return $this->container->get('view')->render($response, 'payment-status.php', $args);
    }

    public function processPayment(Request $request, Response $response, array $args)
    {
        $db = $this->container->get('db');
        $data = $request->getParsedBody();
        $orderId = $data['order_id'];
        $amount = $data['amount'];
        $paymentMethod = $data['payment_method'];

        $db->insert('payments', [
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ]);

        $db->update('orders', ['status' => 'Paid'], ['order_id' => $orderId]);

        return $response->withRedirect('/payment/status');
    }
}
