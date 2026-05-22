<?php
// product-handler.php - Handles product edit and delete operations

session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id'])) {
  $response['message'] = 'Please log in first';
  echo json_encode($response);
  exit;
}

try {
  $mysqli = getDb();
  $user_id = $_SESSION['user_id'];

  // Get user information
  $stmt = $mysqli->prepare('SELECT * FROM users WHERE id = ?');
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if (!$user || $user['role'] !== 'seller') {
    throw new Exception('Unauthorized: You must be a seller to perform this action');
  }

  $seller_name = $user['name'];
  $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

  if ($action === 'delete') {
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id <= 0) {
      throw new Exception('Invalid product ID');
    }

    // Verify product belongs to this seller
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ? AND seller = ?');
    $stmt->bind_param('is', $product_id, $seller_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
      throw new Exception('Product not found or you do not have permission to delete it');
    }

    // Delete product
    $stmt = $mysqli->prepare('DELETE FROM products WHERE id = ? AND seller = ?');
    $stmt->bind_param('is', $product_id, $seller_name);
    $stmt->execute();
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'Product deleted successfully';

  } elseif ($action === 'update') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'] ?? '';

    // Validate inputs
    if ($product_id <= 0 || empty($name) || empty($category) || $price <= 0 || empty($description)) {
      throw new Exception('All fields are required and price must be greater than 0');
    }

    // Verify product belongs to this seller
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ? AND seller = ?');
    $stmt->bind_param('is', $product_id, $seller_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
      throw new Exception('Product not found or you do not have permission to edit it');
    }

    // Update product
    if (!empty($image_url)) {
      $stmt = $mysqli->prepare('UPDATE products SET name = ?, category = ?, price = ?, description = ?, image_url = ? WHERE id = ? AND seller = ?');
      $stmt->bind_param('ssdssi', $name, $category, $price, $description, $image_url, $product_id, $seller_name);
    } else {
      $stmt = $mysqli->prepare('UPDATE products SET name = ?, category = ?, price = ?, description = ? WHERE id = ? AND seller = ?');
      $stmt->bind_param('ssddssi', $name, $category, $price, $description, $product_id, $seller_name);
    }

    $stmt->execute();
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'Product updated successfully';
    $response['product'] = array(
      'id' => $product_id,
      'name' => $name,
      'category' => $category,
      'price' => $price,
      'image_url' => $image_url ?: $product['image_url']
    );

  } elseif ($action === 'get') {
    $product_id = intval($_GET['product_id'] ?? 0);

    if ($product_id <= 0) {
      throw new Exception('Invalid product ID');
    }

    // Get product details (must belong to this seller)
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ? AND seller = ?');
    $stmt->bind_param('is', $product_id, $seller_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
      throw new Exception('Product not found');
    }

    $response['success'] = true;
    $response['product'] = $product;

  } else {
    throw new Exception('Invalid action');
  }

} catch (Exception $e) {
  $response['success'] = false;
  $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>