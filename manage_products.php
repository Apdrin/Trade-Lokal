<?php
require_once __DIR__ . '/auth.php';
requireSeller();
$user = currentUser();
$message = '';
$mysqli = getDb();

// detect if products.owner_id column exists (for backward compatibility)
$hasOwnerColumn = false;
$check = $mysqli->query("SHOW COLUMNS FROM products LIKE 'owner_id'");
if ($check && $check->num_rows > 0) {
  $hasOwnerColumn = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $seller = $user['name'];
    $price = floatval($_POST['price'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($hasOwnerColumn) {
      $stmt = $mysqli->prepare('INSERT INTO products (name, category, seller, price, image_url, description, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
      $stmt->bind_param('sssdssi', $name, $category, $seller, $price, $imageUrl, $description, $user['id']);
    } else {
      $stmt = $mysqli->prepare('INSERT INTO products (name, category, seller, price, image_url, description) VALUES (?, ?, ?, ?, ?, ?)');
      $stmt->bind_param('sssdss', $name, $category, $seller, $price, $imageUrl, $description);
    }
    if ($stmt) {
      $stmt->execute();
      $stmt->close();
    }
    header('Location: manage_products.php');
    exit;
  }

  if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $seller = $user['name'];
    $price = floatval($_POST['price'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $productOwner = null;
    if ($hasOwnerColumn) {
      $stmt = $mysqli->prepare('SELECT owner_id FROM products WHERE id = ? LIMIT 1');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $productOwner = $result->fetch_assoc();
      $stmt->close();

      if (!$productOwner || intval($productOwner['owner_id']) !== intval($user['id'])) {
        header('Location: dashboard.php');
        exit;
      }
    } else {
      // fallback: ensure seller name matches
      $stmt = $mysqli->prepare('SELECT seller FROM products WHERE id = ? LIMIT 1');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $stmt->close();
      if (!$row || $row['seller'] !== $user['name']) {
        header('Location: dashboard.php');
        exit;
      }
    }

    if ($hasOwnerColumn) {
      $stmt = $mysqli->prepare('UPDATE products SET name = ?, category = ?, seller = ?, price = ?, image_url = ?, description = ? WHERE id = ?');
      $stmt->bind_param('sssdssi', $name, $category, $seller, $price, $imageUrl, $description, $id);
    } else {
      $stmt = $mysqli->prepare('UPDATE products SET name = ?, category = ?, seller = ?, price = ?, image_url = ?, description = ? WHERE id = ?');
      $stmt->bind_param('sssdssi', $name, $category, $seller, $price, $imageUrl, $description, $id);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: manage_products.php');
    exit;
  }

  if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);

    if ($hasOwnerColumn) {
      $stmt = $mysqli->prepare('SELECT owner_id FROM products WHERE id = ? LIMIT 1');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $productOwner = $result->fetch_assoc();
      $stmt->close();

      if (!$productOwner || intval($productOwner['owner_id']) !== intval($user['id'])) {
        header('Location: dashboard.php');
        exit;
      }
    } else {
      $stmt = $mysqli->prepare('SELECT seller FROM products WHERE id = ? LIMIT 1');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $stmt->close();
      if (!$row || $row['seller'] !== $user['name']) {
        header('Location: dashboard.php');
        exit;
      }
    }

    $stmt = $mysqli->prepare('DELETE FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_products.php');
    exit;
  }
}

$editProduct = null;
if (isset($_GET['edit_id'])) {
  $editId = intval($_GET['edit_id']);
  if ($hasOwnerColumn) {
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ? AND owner_id = ? LIMIT 1');
    $stmt->bind_param('ii', $editId, $user['id']);
  } else {
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ? AND seller = ? LIMIT 1');
    $stmt->bind_param('is', $editId, $user['name']);
  }
  $stmt->execute();
  $result = $stmt->get_result();
  $editProduct = $result->fetch_assoc();
  $stmt->close();
}

if ($hasOwnerColumn) {
  $stmt = $mysqli->prepare('SELECT * FROM products WHERE owner_id = ? ORDER BY created_at DESC');
  $stmt->bind_param('i', $user['id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $products = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $stmt = $mysqli->prepare('SELECT * FROM products WHERE seller = ? ORDER BY created_at DESC');
  $stmt->bind_param('s', $user['name']);
  $stmt->execute();
  $result = $stmt->get_result();
  $products = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TradeLokal | Manage Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.html"><i class="fas fa-leaf me-2"></i>TradeLokal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_products.php">Manage Products</a></li>
          <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-danger ms-2" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="row">
      <div class="col-lg-5 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="card-title mb-4"><?php echo $editProduct ? 'Edit Product' : 'Add Product'; ?></h3>
            <form method="post" action="manage_products.php">
              <input type="hidden" name="action" value="<?php echo $editProduct ? 'update' : 'create'; ?>">
              <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required
                  value="<?php echo htmlspecialchars($editProduct['name'] ?? '', ENT_QUOTES); ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                  <option value="" selected disabled>-- Select a category --</option>
                  <option value="Food" <?php echo (isset($editProduct['category']) && $editProduct['category'] === 'Food') ? 'selected' : ''; ?>>Local Food</option>
                  <option value="Handmade" <?php echo (isset($editProduct['category']) && $editProduct['category'] === 'Handmade') ? 'selected' : ''; ?>>Handmade Crafts</option>
                  <option value="Clothing" <?php echo (isset($editProduct['category']) && $editProduct['category'] === 'Clothing') ? 'selected' : ''; ?>>Clothing</option>
                  <option value="Agriculture" <?php echo (isset($editProduct['category']) && $editProduct['category'] === 'Agriculture') ? 'selected' : ''; ?>>Agri Products</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Seller</label>
                <input type="text" class="form-control" readonly
                  value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" name="price" step="0.01" class="form-control" required
                  value="<?php echo htmlspecialchars($editProduct['price'] ?? '', ENT_QUOTES); ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Image URL</label>
                <input type="url" name="image_url" class="form-control"
                  value="<?php echo htmlspecialchars($editProduct['image_url'] ?? '', ENT_QUOTES); ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"
                  rows="4"><?php echo htmlspecialchars($editProduct['description'] ?? '', ENT_QUOTES); ?></textarea>
              </div>

              <div class="d-grid gap-2">
                <button type="submit"
                  class="btn btn-orange"><?php echo $editProduct ? 'Update Product' : 'Create Product'; ?></button>
              </div>
            </form>
            <?php if ($editProduct): ?>
              <div class="mt-3">
                <a href="manage_products.php" class="btn btn-secondary btn-sm">Cancel edit</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="card-title mb-4">Products</h3>
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product): ?>
                    <tr>
                      <td><?php echo $product['id']; ?></td>
                      <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?></td>
                      <td><?php echo htmlspecialchars($product['category'], ENT_QUOTES); ?></td>
                      <td><?php echo htmlspecialchars($product['seller'], ENT_QUOTES); ?></td>
                      <td>₱<?php echo number_format($product['price'], 2); ?></td>
                      <td>
                        <a href="manage_products.php?edit_id=<?php echo $product['id']; ?>"
                          class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post" action="manage_products.php"
                          style="display:inline-block; margin-left: 0.5rem;"
                          onsubmit="return confirm('Delete this product?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>