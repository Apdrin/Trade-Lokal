<?php
require_once __DIR__ . '/auth.php';
if (isLoggedIn()) {
  header('Location: dashboard.php');
  exit;
}

$errors = [];
$name = '';
$email = '';
$role = 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirm = trim($_POST['confirm_password'] ?? '');
  $role = trim($_POST['role'] ?? 'customer');

  if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    $errors[] = 'All fields are required.';
  } elseif (strlen($name) < 2) {
    $errors[] = 'Please enter your full name.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
  } elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long.';
  } elseif ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
  } elseif (!in_array($role, ['customer', 'seller'], true)) {
    $errors[] = 'Invalid role selected.';
  } elseif (!registerUser($name, $email, $password, $role)) {
    $errors[] = 'That email is already registered. Please login or use another email.';
  } else {
    loginUser($email, $password);
    header('Location: dashboard.php');
    exit;
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TradeLokal | Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
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
          <li class="nav-item"><a class="nav-link active" href="index.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero">
    <div class="hero-text">
      <h1>Create your TradeLokal account</h1>
      <p>Sign up now and start managing local products with your own account.</p>
    </div>
  </header>

  <main class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-sm rounded-4">
            <div class="card-body p-5">
              <h2 class="card-title mb-4">Sign Up</h2>
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                      <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <form method="post" action="register.php">
                <div class="mb-3">
                  <label for="name" class="form-label">Full Name</label>
                  <input type="text" name="name" class="form-control" id="name" placeholder="Your full name"
                    value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" required />
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com"
                    value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>" required />
                </div>
                <div class="mb-3">
                  <label for="role" class="form-label">Account type</label>
                  <select name="role" id="role" class="form-select" required>
                    <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="seller" <?php echo $role === 'seller' ? 'selected' : ''; ?>>Seller</option>
                  </select>
                  <div class="form-text">Customers browse and buy. Sellers can manage product listings.</div>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" name="password" class="form-control" id="password"
                    placeholder="Create a password" required />
                </div>
                <div class="mb-3">
                  <label for="confirm_password" class="form-label">Confirm Password</label>
                  <input type="password" name="confirm_password" class="form-control" id="confirm_password"
                    placeholder="Repeat your password" required />
                </div>
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-orange">Create Account</button>
                </div>
              </form>
              <div class="mt-4 text-center">
                <p class="mb-1">Already have an account?</p>
                <a href="login.php" class="text-decoration-none" style="color: var(--primary-green)">Sign in here</a>
              </div>
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