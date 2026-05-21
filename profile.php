<?php
require_once __DIR__ . '/auth.php'; // This handles session_start() internally
requireLogin();                     // Uses your built-in guard function

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

// Get user's order count (placeholder - will use this when orders table is created)
$order_count = 0;

// Determine if user is a seller
$is_seller = ($user['role'] === 'seller');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TradeLokal | My Profile</title>
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
            <a class="nav-link active" href="profile.php">
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
    <div class="row">
      <!-- SIDEBAR -->
      <div class="col-lg-3 mb-4">
        <div class="card">
          <div class="card-body text-center">
            <div
              style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--primary-orange); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2em;">
              <i class="fas fa-user"></i>
            </div>
            <h4 class="mb-2"><?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?></h4>
            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?></p>
            <span class="badge" style="background-color: var(--primary-orange); text-transform: capitalize;">
              <?php echo $user['role']; ?>
            </span>
            <hr>
            <p class="text-muted small mb-0">
              Member since: <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
            </p>
          </div>
        </div>

        <!-- MENU -->
        <div class="list-group mt-3">
          <a href="profile.php" class="list-group-item list-group-item-action active">
            <i class="fas fa-user me-2"></i>My Profile
          </a>
          <?php if ($is_seller): ?>
            <a href="seller-products.php" class="list-group-item list-group-item-action">
              <i class="fas fa-box me-2"></i>My Products
            </a>
            <a href="manage_products.php" class="list-group-item list-group-item-action">
              <i class="fas fa-plus-circle me-2"></i>Add Product
            </a>
          <?php endif; ?>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-shopping-bag me-2"></i>Orders
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-heart me-2"></i>Wishlist
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-cog me-2"></i>Settings
          </a>
          <a href="logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
          </a>
        </div>
      </div>

      <!-- MAIN CONTENT -->
      <div class="col-lg-9">
        <!-- PROFILE SECTION -->
        <div class="card mb-4">
          <div class="card-header" style="background-color: var(--primary-orange); color: white;">
            <h5 class="mb-0">Profile Information</h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Full Name</label>
                <input type="text" class="form-control"
                  value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Email Address</label>
                <input type="email" class="form-control"
                  value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>" disabled>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Account Type</label>
                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Member Since</label>
                <input type="text" class="form-control"
                  value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
              </div>
            </div>
            <button class="btn" style="background-color: var(--primary-orange); color: white;">
              <i class="fas fa-edit me-2"></i>Edit Profile
            </button>
          </div>
        </div>

        <!-- QUICK STATS -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card text-center">
              <div class="card-body">
                <i class="fas fa-shopping-bag" style="font-size: 2em; color: var(--primary-orange);"></i>
                <h5 class="mt-2">Total Orders</h5>
                <h3 style="color: var(--primary-orange);">0</h3>
              </div>
            </div>
          </div>
          <?php if ($is_seller): ?>
            <div class="col-md-6">
              <div class="card text-center">
                <div class="card-body">
                  <i class="fas fa-box" style="font-size: 2em; color: var(--primary-green);"></i>
                  <h5 class="mt-2">Active Products</h5>
                  <h3 style="color: var(--primary-green);">
                    <?php
                    $stmt = $mysqli->prepare('SELECT COUNT(*) as count FROM products WHERE seller = ?');
                    $stmt->bind_param('s', $user['name']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    echo $row['count'];
                    $stmt->close();
                    ?>
                  </h3>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- SELLER SECTION -->
        <?php if ($is_seller): ?>
          <div class="card">
            <div class="card-header" style="background-color: var(--primary-green); color: white;">
              <h5 class="mb-0">Seller Dashboard</h5>
            </div>
            <div class="card-body">
              <p class="text-muted mb-3">Manage your products and sales from here</p>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <a href="manage_products.php" class="btn btn-outline-primary w-100">
                    <i class="fas fa-plus-circle me-2"></i>Add New Product
                  </a>
                </div>
                <div class="col-md-6 mb-2">
                  <a href="seller-products.php" class="btn btn-outline-success w-100">
                    <i class="fas fa-list me-2"></i>View My Products
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="card">
            <div class="card-header" style="background-color: var(--primary-green); color: white;">
              <h5 class="mb-0">Become a Seller</h5>
            </div>
            <div class="card-body">
              <p>Want to sell your local products? Upgrade your account to seller and start making sales!</p>
              <button class="btn" style="background-color: var(--primary-orange); color: white;">
                <i class="fas fa-arrow-up me-2"></i>Upgrade to Seller
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="bg-dark text-white text-center py-4 mt-50">
    <div class="container">
      <p>&copy; 2026 TradeLokal | Buy Local, Support Communities</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>

</html>