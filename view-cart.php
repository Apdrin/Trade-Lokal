<?php
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = array();
}

// Calculate totals
$subtotal = 0;
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
  $subtotal += $item['price'] * $item['quantity'];
  $total_items += $item['quantity'];
}
$shipping = 0; // Free shipping for now
$tax = $subtotal * 0.12; // 12% tax
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TradeLokal | Shopping Cart</title>
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
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- PAGE CONTENT -->
  <div class="container py-5">
    <h1 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h1>

    <?php if (empty($_SESSION['cart'])): ?>
      <div class="alert alert-info text-center py-5">
        <h4><i class="fas fa-inbox me-2"></i>Your cart is empty</h4>
        <p class="mb-3">Start shopping to add products to your cart.</p>
        <a href="index.php" class="btn" style="background-color: var(--primary-orange); color: white;">
          <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
        </a>
      </div>
    <?php else: ?>
      <div class="row">
        <!-- CART ITEMS -->
        <div class="col-lg-8 mb-4">
          <div class="card">
            <div class="card-body">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?php echo htmlspecialchars($item['image_url'], ENT_QUOTES); ?>"
                            alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>"
                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                          <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($item['seller'], ENT_QUOTES); ?></small>
                          </div>
                        </div>
                      </td>
                      <td>₱<?php echo number_format($item['price'], 2); ?></td>
                      <td>
                        <div class="d-flex gap-2 align-items-center" style="width: 120px;">
                          <input type="number" class="form-control form-control-sm" value="<?php echo $item['quantity']; ?>"
                            min="1" max="100" style="width: 60px;"
                            onchange="updateCart(<?php echo $product_id; ?>, this.value)">
                        </div>
                      </td>
                      <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                      <td>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(<?php echo $product_id; ?>)">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ACTION BUTTONS -->
          <div class="mt-3">
            <a href="index.php" class="btn btn-outline-primary">
              <i class="fas fa-arrow-left me-2"></i>Continue Shopping
            </a>
            <button class="btn btn-outline-danger" onclick="clearCart()">
              <i class="fas fa-trash me-2"></i>Clear Cart
            </button>
          </div>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="col-lg-4">
          <div class="card sticky-top" style="top: 100px;">
            <div class="card-body">
              <h5 class="card-title">Order Summary</h5>
              <hr>

              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal (<?php echo $total_items; ?> items):</span>
                <span>₱<?php echo number_format($subtotal, 2); ?></span>
              </div>

              <div class="d-flex justify-content-between mb-2">
                <span>Shipping:</span>
                <span class="badge bg-success">FREE</span>
              </div>

              <div class="d-flex justify-content-between mb-3">
                <span>Tax (12%):</span>
                <span>₱<?php echo number_format($tax, 2); ?></span>
              </div>

              <hr>

              <div class="d-flex justify-content-between mb-4">
                <strong>Total:</strong>
                <strong
                  style="font-size: 1.3em; color: var(--primary-orange);">₱<?php echo number_format($total, 2); ?></strong>
              </div>

              <button class="btn btn-lg w-100" style="background-color: var(--primary-orange); color: white;"
                onclick="checkout()">
                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
              </button>

              <div class="mt-3 text-center">
                <small class="text-muted">
                  <i class="fas fa-lock me-1"></i>Secure checkout
                </small>
              </div>
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
    function updateCart(productId, quantity) {
      fetch('cart-handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        });
    }

    function removeFromCart(productId) {
      if (confirm('Remove this item from cart?')) {
        fetch('cart-handler.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `action=remove&product_id=${productId}`
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          });
      }
    }

    function clearCart() {
      if (confirm('Clear entire cart?')) {
        // Implement clear cart by removing all items
        const items = document.querySelectorAll('tbody tr');
        items.forEach(row => {
          const removeBtn = row.querySelector('button[onclick*="removeFromCart"]');
          if (removeBtn) removeBtn.click();
        });
      }
    }

    function checkout() {
      <?php if (isset($_SESSION['user_id'])): ?>
        fetch('cart-handler.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'action=checkout'
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Order placed successfully! Order ID: ' + data.order_id);
              window.location.href = 'view-cart.php';
            } else {
              alert('Error: ' + data.message);
            }
          });
      <?php else: ?>
        alert('Please log in to checkout');
        window.location.href = 'login.php';
      <?php endif; ?>
    }
  </script>
  <script src="script.js"></script>
</body>

</html>