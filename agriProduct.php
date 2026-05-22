<?php
session_start();
require_once __DIR__ . '/db.php';
$mysqli = getDb();
$category = 'Agriculture';
$stmt = $mysqli->prepare('SELECT * FROM products WHERE category = ? ORDER BY created_at DESC');
$stmt->bind_param('s', $category);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TradeLokal | Agri Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body class="agri-products">
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-leaf me-2"></i>TradeLokal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
          <li class="nav-item"><a class="nav-link" href="view-cart.php">
              <i class="fas fa-shopping-cart me-1"></i>Cart
              <?php if ($cart_count > 0): ?>
                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
              <?php endif; ?>
            </a></li>
          <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-success ms-2" href="login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- CATEGORIES SECTION -->
  <section id="categories" class="py-5">
    <div class="container">
      <h2 class="text-center mb-4 fw-bold" style="color: var(--primary-green);">Shop by Category</h2>
      <div class="row g-4">
        <!-- Category 1 -->
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="food.php" class="button">
              <div class="cat-icon"><i class="fas fa-apple-alt"></i></div>
              <h5>Local Food</h5>
            </a>
          </div>
        </div>
        <!-- Category 2 -->
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="handmade.php" class="button">
              <div class="cat-icon"><i class="fas fa-paint-brush"></i></div>
              <h5>Handmade Crafts</h5>
            </a>
          </div>
        </div>
        <!-- Category 3 -->
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="clothing.php" class="button">
              <div class="cat-icon"><i class="fas fa-tshirt"></i></div>
              <h5>Clothing</h5>
            </a>
          </div>
        </div>
        <!-- Category 4 -->
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="agriProduct.php" class="button">
              <div class="cat-icon"><i class="fas fa-seedling"></i></div>
              <h5>Agri Products</h5>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Hero Section -->
  <header class="hero">
    <div class="hero-text">
      <h1>Agri Products</h1>
      <p>Fresh produce and agricultural supplies from local farmers.</p>
      <div class="search-bar">
        <input type="text" id="categorySearchInput" placeholder="Search agri products (e.g., Seeds, Fertilizer)...">
        <button class="search-btn">Search</button>
      </div>
    </div>
  </header>

  <!-- Product Display Section -->
  <section class="products">
    <div class="container">
      <h2 class="text-center mb-4 fw-bold" style="color: var(--primary-orange);">Available Agri Products</h2>
      <div class="row g-4" id="product-list">
        <?php if (empty($products)): ?>
          <div class="col-12 text-center">
            <p class="lead text-muted">No products available in this category yet.</p>
          </div>
        <?php else: ?>
          <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-3 product-item"
              data-name="<?php echo htmlspecialchars(strtolower($product['name']), ENT_QUOTES); ?>">
              <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES); ?>"
                  alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>" class="product-img">
                <div class="p-3">
                  <h5 class="mb-1"><?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?></h5>
                  <p class="seller-tag mb-2"><i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($product['seller'], ENT_QUOTES); ?></p>
                  <p class="small text-muted mb-2"><?php echo htmlspecialchars($product['description'], ENT_QUOTES); ?></p>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="product-price">₱<?php echo number_format($product['price'], 2); ?></span>
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-orange">View
                      Details</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; 2026 TradeLokal | Buy Local, Support Communities</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>

</html>