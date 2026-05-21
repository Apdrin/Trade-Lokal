<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TradeLokal | Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-leaf me-2"></i>TradeLokal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_products.php">Manage Products</a></li>
          <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-danger ms-2" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
          <div class="card shadow-sm rounded-4 p-4">
            <h2 class="mb-3">Welcome, <?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>!</h2>
            <p class="lead mb-4">You are logged in as a
              <strong><?php echo htmlspecialchars(ucfirst($user['role']), ENT_QUOTES); ?></strong> with
              <strong><?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?></strong>.
            </p>
            <div class="row g-4">
              <?php if ($user['role'] === 'seller'): ?>
                <div class="col-md-6">
                  <div class="card border-success h-100">
                    <div class="card-body">
                      <h5 class="card-title">Manage Products</h5>
                      <p class="card-text">Add, edit, or delete your marketplace product listings.</p>
                      <a href="manage_products.php" class="btn btn-success">Go to Products</a>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <div class="col-md-6">
                <div class="card border-primary h-100">
                  <div class="card-body">
                    <h5 class="card-title">Browse Local Marketplace</h5>
                    <p class="card-text">Explore local products and offers from the community.</p>
                    <a href="index.php" class="btn btn-primary">Browse Products</a>
                  </div>
                </div>
              </div>
              <?php if ($user['role'] !== 'seller'): ?>
                <div class="col-md-6">
                  <div class="card border-warning h-100">
                    <div class="card-body">
                      <h5 class="card-title">Want to sell?</h5>
                      <p class="card-text">Become a seller and manage your own product listings. Logout and register as a
                        seller account.</p>
                      <a href="logout.php" class="btn btn-warning">Logout</a>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <p>&copy; 2026 TradeLokal | Buy Local, Support Communities</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>