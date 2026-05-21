<?php
require_once __DIR__ . '/auth.php';
requireSeller(); // Automatically checks if logged in AND if they are a seller

require_once __DIR__ . '/db.php';
$mysqli = getDb();

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user is a seller
if (!$user || $user['role'] !== 'seller') {
  header('Location: profile.php');
  exit;
}
$seller_name = $user['name'];
$stmt = $mysqli->prepare('SELECT * FROM products WHERE seller = ? ORDER BY created_at DESC');
$stmt->bind_param('s', $seller_name);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TradeLokal | My Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-leaf me-2"></i>TradeLokal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- PAGE CONTENT -->
  <div class="container py-5">
    <div class="row mb-4">
      <div class="col-md-8">
        <h1><i class="fas fa-box me-2"></i>My Products</h1>
        <p class="text-muted">Manage all your products here</p>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="manage_products.php" class="btn" style="background-color: var(--primary-orange); color: white;">
          <i class="fas fa-plus-circle me-2"></i>Add New Product
        </a>
      </div>
    </div>

    <?php if (empty($products)): ?>
      <div class="alert alert-info text-center py-5">
        <h4><i class="fas fa-box-open me-2"></i>No Products Yet</h4>
        <p class="mb-3">You haven't added any products yet. Start selling today!</p>
        <a href="manage_products.php" class="btn" style="background-color: var(--primary-orange); color: white;">
          <i class="fas fa-plus-circle me-2"></i>Add Your First Product
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead style="background-color: var(--primary-orange); color: white;">
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <img src="<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES); ?>"
                      alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>"
                      style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                    <div>
                      <h6 class="mb-0"><?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?></h6>
                      <small
                        class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50), ENT_QUOTES); ?>...</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge" style="background-color: var(--primary-green);">
                    <?php echo htmlspecialchars($product['category'], ENT_QUOTES); ?>
                  </span>
                </td>
                <td>
                  <strong>₱<?php echo number_format($product['price'], 2); ?></strong>
                </td>
                <td>
                  <small><?php echo date('M d, Y', strtotime($product['created_at'])); ?></small>
                </td>
                <td>
                  <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-warning" title="Edit" onclick="editProduct(<?php echo $product['id']; ?>)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" title="Delete"
                    onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>')">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- STATS -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card text-center">
            <div class="card-body">
              <i class="fas fa-box" style="font-size: 2em; color: var(--primary-orange);"></i>
              <h5 class="mt-2">Total Products</h5>
              <h3 style="color: var(--primary-orange);"><?php echo count($products); ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card text-center">
            <div class="card-body">
              <i class="fas fa-coins" style="font-size: 2em; color: var(--primary-green);"></i>
              <h5 class="mt-2">Total Sales Value</h5>
              <h3 style="color: var(--primary-green);">
                ₱<?php
                $total = 0;
                foreach ($products as $p) {
                  $total += $p['price'];
                }
                echo number_format($total, 2);
                ?>
              </h3>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- FOOTER -->
  <footer class="bg-dark text-white text-center py-4 mt-50">
    <div class="container">
      <p>&copy; 2026 TradeLokal | Buy Local, Support Communities</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function deleteProduct(productId, productName) {
      if (confirm('Are you sure you want to delete "' + productName + '"?')) {
        // TODO: Implement delete functionality
        alert('Product deletion coming soon!');
      }
    }

    function editProduct(productId) {
      // TODO: Implement edit functionality
      alert('Product editing coming soon!');
    }
  </script>
  <script src="script.js"></script>
</body>

</html>