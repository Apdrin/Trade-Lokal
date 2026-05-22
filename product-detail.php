<?php
session_start();
require_once __DIR__ . '/db.php';
$mysqli = getDb();

// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
  header('Location: index.php');
  exit;
}

// Fetch product details from database
$stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ?');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Redirect if product not found
if (!$product) {
  header('Location: index.php');
  exit;
}

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
  <title>TradeLokal | <?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <!-- Navigation -->
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

  <!-- Product Detail Section -->
  <section class="py-5">
    <div class="container">
      <!-- Back Button -->
      <div class="mb-4">
        <a href="javascript:history.back()" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-2"></i>Back
        </a>
      </div>

      <!-- Product Detail -->
      <div class="row g-5">
        <!-- Product Image -->
        <div class="col-md-5">
          <div class="position-sticky" style="top: 100px;">
            <img src="<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES); ?>"
              alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>" class="img-fluid rounded shadow-sm"
              style="width: 100%; object-fit: cover;">
          </div>
        </div>

        <!-- Product Information -->
        <div class="col-md-7">
          <!-- Product Name & Category -->
          <h1 class="mb-2"><?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?></h1>
          <div class="mb-3">
            <span class="badge bg-info" style="background-color: var(--primary-orange) !important; font-size: 0.9rem;">
              <?php echo htmlspecialchars($product['category'], ENT_QUOTES); ?>
            </span>
          </div>

          <!-- Seller Information -->
          <div class="card border-0 bg-light p-3 mb-4">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i class="fas fa-user-circle" style="font-size: 2.5rem; color: var(--primary-orange);"></i>
              </div>
              <div>
                <p class="mb-0 text-muted small">Sold by</p>
                <h5 class="mb-0"><?php echo htmlspecialchars($product['seller'], ENT_QUOTES); ?></h5>
              </div>
            </div>
          </div>

          <!-- Price -->
          <div class="mb-4">
            <p class="text-muted mb-2">Price</p>
            <h2 style="color: var(--primary-orange);">₱<?php echo number_format($product['price'], 2); ?></h2>
          </div>

          <!-- Quantity Selector -->
          <div class="mb-4">
            <label class="form-label" for="quantity">Quantity</label>
            <div class="d-flex gap-2 align-items-center">
              <input type="number" id="quantity" class="form-control" style="width: 80px;" value="1" min="1" max="100">
              <span class="text-muted">Available</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-grid gap-2 d-sm-flex mb-5">
            <button class="btn btn-lg" style="background-color: var(--primary-orange); color: white;"
              onclick="addToCart(<?php echo $product['id']; ?>)">
              <i class="fas fa-shopping-cart me-2"></i>Add to Cart
            </button>
            <button class="btn btn-lg btn-outline-secondary">
              <i class="fas fa-heart me-2"></i>Save for Later
            </button>
          </div>

          <!-- Product Description -->
          <div class="mb-5">
            <h4 class="mb-3">Product Description</h4>
            <p class="lead" style="line-height: 1.8;">
              <?php echo nl2br(htmlspecialchars($product['description'], ENT_QUOTES)); ?>
            </p>
          </div>

          <!-- Additional Details -->
          <div class="row g-4 mb-5">
            <div class="col-sm-6">
              <div class="border-start border-4 ps-3" style="border-color: var(--primary-orange);">
                <p class="text-muted mb-1 small">Category</p>
                <h6 class="mb-0"><?php echo htmlspecialchars($product['category'], ENT_QUOTES); ?></h6>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="border-start border-4 ps-3" style="border-color: var(--primary-orange);">
                <p class="text-muted mb-1 small">Availability</p>
                <h6 class="mb-0"><i class="fas fa-check-circle" style="color: #28a745;"></i> In Stock</h6>
              </div>
            </div>
          </div>

          <!-- Share Section -->
          <div class="pt-4 border-top">
            <p class="mb-3">Share this product:</p>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary btn-sm">
                <i class="fab fa-facebook"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm">
                <i class="fab fa-twitter"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm">
                <i class="fab fa-linkedin"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm">
                <i class="fab fa-whatsapp"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products Section -->
      <hr class="my-5">
      <div class="mt-5">
        <h3 class="mb-4 fw-bold" style="color: var(--primary-green);">More from
          <?php echo htmlspecialchars($product['seller'], ENT_QUOTES); ?>
        </h3>
        <div class="row g-4">
          <?php
          // Fetch related products from the same seller
          $seller = $product['seller'];
          $stmt = $mysqli->prepare('SELECT * FROM products WHERE seller = ? AND id != ? LIMIT 4');
          $stmt->bind_param('si', $seller, $product_id);
          $stmt->execute();
          $result = $stmt->get_result();
          $related_products = $result->fetch_all(MYSQLI_ASSOC);
          $stmt->close();

          if (empty($related_products)):
            ?>
            <div class="col-12">
              <p class="text-muted">No other products from this seller.</p>
            </div>
          <?php else: ?>
            <?php foreach ($related_products as $related): ?>
              <div class="col-md-6 col-lg-3">
                <div class="product-card">
                  <img src="<?php echo htmlspecialchars($related['image_url'], ENT_QUOTES); ?>"
                    alt="<?php echo htmlspecialchars($related['name'], ENT_QUOTES); ?>" class="product-img">
                  <div class="p-3">
                    <h5 class="mb-1"><?php echo htmlspecialchars($related['name'], ENT_QUOTES); ?></h5>
                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($related['description'], ENT_QUOTES); ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="product-price">₱<?php echo number_format($related['price'], 2); ?></span>
                      <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-sm btn-orange">View
                        Details</a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <p>&copy; 2026 TradeLokal | Buy Local, Support Communities</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function addToCart(productId) {
      const quantity = document.getElementById('quantity').value;

      if (quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
      }

      fetch('cart-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Product added to cart! Items in cart: ' + data.cart_count);
            // Reset quantity
            document.getElementById('quantity').value = 1;
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Failed to add product to cart');
        });
    }
  </script>
  <script src="script.js"></script>
</body>

</html>