<?php
// cart-handler.php - Handles cart operations via AJAX or form submission

session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && !isset($_SESSION['cart'])) {
  $_SESSION['cart'] = array();
}

$response = array('success' => false, 'message' => '');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
  $mysqli = getDb();

  if ($action === 'add') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($product_id <= 0 || $quantity <= 0) {
      throw new Exception('Invalid product or quantity');
    }

    // Fetch product details
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
      throw new Exception('Product not found');
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
      $_SESSION['cart'] = array();
    }

    // Add or update cart
    if (isset($_SESSION['cart'][$product_id])) {
      $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
      $_SESSION['cart'][$product_id] = array(
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image_url' => $product['image_url'],
        'seller' => $product['seller'],
        'quantity' => $quantity
      );
    }

    // Calculate cart total
    $cart_total = 0;
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
      $cart_total += $item['price'] * $item['quantity'];
      $cart_count += $item['quantity'];
    }

    $response['success'] = true;
    $response['message'] = 'Product added to cart!';
    $response['cart_count'] = $cart_count;
    $response['cart_total'] = $cart_total;

  } elseif ($action === 'remove') {
    $product_id = intval($_POST['product_id'] ?? 0);

    if (isset($_SESSION['cart'][$product_id])) {
      unset($_SESSION['cart'][$product_id]);
      $response['success'] = true;
      $response['message'] = 'Product removed from cart';
    } else {
      throw new Exception('Product not in cart');
    }

  } elseif ($action === 'update') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($quantity <= 0) {
      unset($_SESSION['cart'][$product_id]);
      $response['success'] = true;
      $response['message'] = 'Product removed from cart';
    } else {
      if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        $response['success'] = true;
        $response['message'] = 'Cart updated';
      } else {
        throw new Exception('Product not in cart');
      }
    }

  } elseif ($action === 'get') {
    $response['success'] = true;
    $response['cart'] = $_SESSION['cart'] ?? array();
    $cart_count = 0;
    $cart_total = 0;
    foreach ($_SESSION['cart'] ?? array() as $item) {
      $cart_count += $item['quantity'];
      $cart_total += $item['price'] * $item['quantity'];
    }
    $response['cart_count'] = $cart_count;
    $response['cart_total'] = $cart_total;

  } elseif ($action === 'checkout') {
    if (!isset($_SESSION['user_id'])) {
      throw new Exception('Please log in to checkout');
    }

    $cart = $_SESSION['cart'] ?? array();
    if (empty($cart)) {
      throw new Exception('Cart is empty');
    }

    // Create orders table if it doesn't exist
    $mysqli->query("CREATE TABLE IF NOT EXISTS orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Create order items table if it doesn't exist
    $mysqli->query("CREATE TABLE IF NOT EXISTS order_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Calculate total
    $total = 0;
    foreach ($cart as $item) {
      $total += $item['price'] * $item['quantity'];
    }

    // Insert order
    $user_id = $_SESSION['user_id'];
    $stmt = $mysqli->prepare('INSERT INTO orders (user_id, total_amount) VALUES (?, ?)');
    $stmt->bind_param('id', $user_id, $total);
    $stmt->execute();
    $order_id = $mysqli->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $mysqli->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($cart as $product_id => $item) {
      $stmt->bind_param('iiii', $order_id, $product_id, $item['quantity'], $item['price']);
      $stmt->execute();
    }
    $stmt->close();

    // Clear cart
    $_SESSION['cart'] = array();

    $response['success'] = true;
    $response['message'] = 'Order placed successfully!';
    $response['order_id'] = $order_id;
  } else {
    throw new Exception('Invalid action');
  }

} catch (Exception $e) {
  $response['success'] = false;
  $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>