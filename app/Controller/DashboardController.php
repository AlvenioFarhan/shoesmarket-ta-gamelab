<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;

class DashboardController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function orderDetail($request, $response, $args)
    {
        $db = $this->container->get('db');
        $users = $db->select('users', [
            '[>]roles' => ['role_id' => 'id'],
        ], [
            'users.id',
            'users.username',
            'users.email',
            'users.password',
            'roles.role_name',
        ]);

        // Mendapatkan nama pengguna dari session admin
        $username = isset($_SESSION['admin']['username']) ? $_SESSION['admin']['username'] : 'Guest';

        // Mengambil data orders dan join dengan users dan payments
        $orders = $db->select('orders', [
            '[>]users' => ['user_id' => 'id'],
            '[>]payments' => ['orders.order_id' => 'order_id'],
        ], [
            'orders.order_id',
            'users.username',
            'users.email',
            'orders.total(total_price)',
            'payments.payment_method',
            'orders.status',
        ]);

        return $this->container->get('view')->render($response, 'orderDetail-dashboardAdmin.php', [
            'orders' => $orders,
            'users' => $users,
            'username' => $username, // Kirimkan username ke template
        ]);
    }

    public function confirmOrder($request, $response, $args)
    {
        $db = $this->container->get('db');
        $data = $request->getParsedBody();
        $orderId = $data['order_id'];

        // Update order status to confirmed
        $updateStatus = $db->update('orders', ['status' => 'Confirmed'], ['order_id' => $orderId]);

        if ($updateStatus->rowCount() > 0) {
            $orderDetails = $db->select('order_details', '*', ['order_id' => $orderId]);
            foreach ($orderDetails as $item) {
                // Check the stock quantity
                $product = $db->get('products', '*', ['product_id' => $item['product_id']]);
                if ($product['stock_quantity'] < $item['quantity']) {
                    // If stock is not enough, rollback the status update and return error
                    $db->update('orders', ['status' => 'Pending'], ['order_id' => $orderId]);
                    return $response->withJson(['success' => false, 'message' => 'Insufficient stock for product ID ' . $item['product_id']], 400);
                }
            }

            // If all items have sufficient stock, proceed to update stock quantities
            foreach ($orderDetails as $item) {
                $db->update('products', [
                    'stock_quantity[-]' => $item['quantity'],
                ], [
                    'product_id' => $item['product_id'],
                ]);
            }

            return $response->withJson(['success' => true, 'status' => 'Confirmed']);
        } else {
            return $response->withJson(['success' => false], 500);
        }
    }

    public function cancelOrder($request, $response, $args)
    {
        $db = $this->container->get('db');
        $data = $request->getParsedBody();
        $orderId = $data['order_id'];

        // Update order status to canceled
        $updateStatus = $db->update('orders', ['status' => 'Canceled'], ['order_id' => $orderId]);

        if ($updateStatus->rowCount() > 0) {
            return $response->withJson(['success' => true, 'status' => 'Canceled']);
        } else {
            return $response->withJson(['success' => false], 500);
        }
    }

    public function getOrderDetails($request, $response, $args)
    {
        $db = $this->container->get('db');
        $orderId = $args['id'];

        // Mengambil detail order berdasarkan order_id
        $orderDetails = $db->select('order_details', [
            '[>]products' => ['product_id' => 'product_id'],
        ], [
            'order_details.order_id',
            'order_details.product_id',
            'products.name(product_name)',
            'products.image',
            'order_details.quantity',
            'order_details.price',
        ], [
            'order_details.order_id' => $orderId,
        ]);

        return $response->withJson($orderDetails);
    }

    public function dataUser($request, $response, $args)
    {
        $db = $this->container->get('db');
        $users = $db->select('users', [
            '[>]roles' => ['role_id' => 'id'],
        ], [
            'users.id',
            'users.username',
            'users.email',
            'users.password',
            'roles.role_name',
        ]);

        // Mendapatkan nama pengguna dari session admin
        $username = isset($_SESSION['admin']['username']) ? $_SESSION['admin']['username'] : 'Guest';

        return $this->container->get('view')->render($response, 'dashboardAdmin.php', [
            'users' => $users,
            'username' => $username, // Kirimkan username ke template
        ]);
    }

    public function addUserForm($request, $response, $args)
    {
        $db = $this->container->get('db');
        $roles = $db->select('roles', '*');
        return $this->container->get('view')->render($response, 'tambahUser-dashboardAdmin.php', [
            'roles' => $roles,
        ]);
    }

    public function processAddUser($request, $response, $args)
    {
        $db = $this->container->get('db');

        // Ambil data dari formulir
        $data = $request->getParsedBody();

        // Mengenkripsi password menggunakan JWT
        $key = "your_secret_key";
        $payload = array(
            "password" => $data['password'],
        );
        $algorithm = 'HS256';
        $jwt = JWT::encode($payload, $key, $algorithm);

        // Masukkan data ke database
        $db->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $jwt,
            'role_id' => $data['role_id'],
        ]);

        return $response->withRedirect('/dashboardAdmin');
    }

    public function editUser($request, $response, $args)
    {
        $db = $this->container->get('db');
        $user = $db->get('users', '*', ['id' => $args['id']]);
        $roles = $db->select('roles', '*');
        return $this->container->get('view')->render($response, 'editUser-dashboardAdmin.php', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function processEditUser($request, $response, $args)
    {
        $db = $this->container->get('db');

        // Ambil data dari formulir
        $data = $request->getParsedBody();

        // Mengenkripsi password menggunakan JWT
        $key = "your_secret_key";
        $payload = array(
            "password" => $data['password'],
        );
        $algorithm = 'HS256';
        $jwt = JWT::encode($payload, $key, $algorithm);

        // Update data di database
        $db->update('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $jwt,
            'role_id' => $data['role_id'],
        ], ['id' => $args['id']]);

        return $response->withRedirect('/dashboardAdmin');
    }

    public function deleteUser($request, $response, $args)
    {
        $db = $this->container->get('db');
        $db->delete('users', ['id' => $args['id']]);
        return $response->withRedirect('/dashboardAdmin');
    }

    public function dataProduct($request, $response, $args)
    {
        $db = $this->container->get('db');
        $products = $db->select('products', '*');

        // Mendapatkan nama pengguna dari session admin
        $username = isset($_SESSION['admin']['username']) ? $_SESSION['admin']['username'] : 'Guest';

        return $this->container->get('view')->render($response, 'dataBarang-dashboardAdmin.php', [
            'products' => $products,
            'username' => $username, // Kirimkan username ke template
        ]);
    }

    public function addProduct($request, $response, $args)
    {
        $db = $this->container->get('db');
        $categories = $db->select('categories', '*');
        return $this->container->get('view')->render($response, 'tambahBarang-dashboardAdmin.php', [
            'categories' => $categories,
        ]);
    }

    public function processAddProduct($request, $response, $args)
    {
        $db = $this->container->get('db');

        // Ambil data dari formulir
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $image = $uploadedFiles['image'];

        // Hapus field 'simpan' dari data jika ada
        unset($data['simpan']);

        // Proses upload file
        if ($image->getError() === UPLOAD_ERR_OK) {
            $directory = __DIR__ . '/../../public/asset/image/uploads'; // Pastikan path ini benar
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true); // Buat direktori jika belum ada
            }
            $filename = $this->moveUploadedFile($directory, $image);
            $data['image'] = $filename;
        } else {
            $data['image'] = ''; // Default value if no image is uploaded
        }

        // Masukkan data ke database
        $db->insert('products', [
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'],
            'category_id' => $data['category_id'],
            'image' => $data['image'],
        ]);

        return $response->withRedirect('/dashboardAdmin/data-barang');
    }

    private function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // Generate a random name
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $filepath = $directory . DIRECTORY_SEPARATOR . $filename;

        $uploadedFile->moveTo($filepath);
        error_log("File uploaded: " . $filepath);
        return $filename;
    }

    public function editProduct($request, $response, $args)
    {
        $db = $this->container->get('db');
        $product = $db->get('products', '*', ['product_id' => $args['id']]);
        $categories = $db->select('categories', '*');

        return $this->container->get('view')->render($response, 'editBarang-dashboardAdmin.php', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function processEditProduct($request, $response, $args)
    {
        $db = $this->container->get('db');

        // Ambil data dari formulir
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $image = $uploadedFiles['image'];

        // Hapus field 'simpan' dari data jika ada
        unset($data['simpan']);

        // Dapatkan data produk yang ada
        $existingProduct = $db->get('products', '*', ['product_id' => $args['id']]);

        // Proses upload file
        if ($image->getError() === UPLOAD_ERR_OK) {
            $directory = __DIR__ . '/../../public/asset/image/uploads'; // Pastikan path ini benar
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true); // Buat direktori jika belum ada
            }
            $filename = $this->moveUploadedFile($directory, $image);
            $data['image'] = $filename;

            // Hapus file lama jika ada
            if ($existingProduct['image']) {
                $oldFilepath = $directory . DIRECTORY_SEPARATOR . $existingProduct['image'];
                if (file_exists($oldFilepath)) {
                    unlink($oldFilepath);
                }
            }
        } else {
            // Jika tidak ada gambar baru, gunakan gambar lama
            $data['image'] = $existingProduct['image'];
        }

        // Update data di database
        $db->update('products', $data, ['product_id' => $args['id']]);

        return $response->withRedirect('/dashboardAdmin/data-barang');
    }

    public function deleteProduct($request, $response, $args)
    {
        $db = $this->container->get('db');
        $product = $db->get('products', '*', ['product_id' => $args['id']]);

        // Hapus file gambar jika ada
        $directory = __DIR__ . '/../../public/asset/image/uploads';
        if ($product['image']) {
            $filepath = $directory . DIRECTORY_SEPARATOR . $product['image'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        // Hapus produk dari database
        $db->delete('products', ['product_id' => $args['id']]);

        return $response->withRedirect('/dashboardAdmin/data-barang');
    }
}
