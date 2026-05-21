<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db.php';
$mysqli = getDb();

// Fetch featured products (latest 6)
$featured_products = array();
if ($mysqli) {
  $stmt = $mysqli->prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT 6');
  if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $featured_products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TradeLokal | Buy Local, Support Communities</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- 1. NAVBAR -->
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
          <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-success ms-2" href="profile.php"> <i
                class="fas fa-user me-1"></i>Profile</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- 2. HERO SECTION -->
  <header class="hero-section">
    <div class="container">
      <h1 class="display-4 fw-bold mb-3">Buy Local. Support Communities.</h1>
      <p class="lead mb-4">Discover unique products from small businesses in your area.</p>
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="input-group input-group-lg shadow">
            <input type="text" id="searchInput" class="form-control border-0"
              placeholder="Search for products (e.g., Banana, Basket)...">
            <button class="btn btn-warning text-dark fw-bold px-4" type="button" onclick="filterProducts()">
              <i class="fas fa-search"></i> Search
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- 3. CATEGORIES SECTION -->
  <section id="categories" class="py-5">
    <div class="container">
      <h2 class="text-center mb-4 fw-bold" style="color: var(--primary-green);">Shop by Category</h2>
      <div class="row g-4">
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="food.php" class="button">
              <div class="cat-icon"><i class="fas fa-apple-alt"></i></div>
              <h5>Local Food</h5>
            </a>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="handmade.php" class="button">
              <div class="cat-icon"><i class="fas fa-paint-brush"></i></div>
              <h5>Handmade Crafts</h5>
            </a>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="category-card p-4 text-center">
            <a href="clothing.php" class="button">
              <div class="cat-icon"><i class="fas fa-tshirt"></i></div>
              <h5>Clothing</h5>
            </a>
          </div>
        </div>
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

  <!-- 4. PRODUCT GRID SECTION -->
  <section id="products" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4 fw-bold" style="color: var(--primary-green);">Featured Products</h2>
      <div class="row g-4" id="productGrid">
        <?php if (empty($featured_products)): ?>
          <div class="col-12 text-center">
            <p class="lead text-muted">No products available yet. Check back soon!</p>
          </div>
        <?php else: ?>
          <?php foreach ($featured_products as $product): ?>
            <div class="col-md-6 col-lg-3 product-item">
              <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES); ?>"
                  alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>" class="product-img">
                <div class="p-3">
                  <h5 class="mb-1">
                    <?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>
                  </h5>
                  <p class="seller-tag mb-2"><i class="fas fa-user"></i>
                    <?php echo htmlspecialchars($product['seller'], ENT_QUOTES); ?>
                  </p>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="product-price">₱
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
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

  <!-- 5. CTA SECTION -->
  <section class="cta-section py-5" style="background-color: var(--primary-green); color: white;">
    <div class="container text-center">
      <h2 class="mb-3">Are you a seller?</h2>
      <p class="lead mb-4">Join our community and start selling your local products today!</p>
      <a href="register.php" class="btn btn-light btn-lg">Get Started</a>
    </div>
  </section>

  <!-- 6. FOOTER -->
  <footer class="bg-dark text-white text-center py-4">
    <div class="container">
      <p>&copy; 2026 TradeLokal | Buy Local, Support Communities</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>

</html>