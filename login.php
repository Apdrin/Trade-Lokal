<?php
require_once __DIR__ . '/auth.php';
if (isLoggedIn()) {
  header('Location: profile.php');
  exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($email === '' || $password === '') {
    $errors[] = 'Please enter both email and password.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
  } elseif (!loginUser($email, $password)) {
    $errors[] = 'Invalid email or password.';
  } else {
    header('Location: profile.php');
    exit;
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TradeLokal | Login</title>
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
          <li class="nav-item"><a class="nav-link" href="register.php">Sign Up</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero">
    <div class="hero-text">
      <h1>Login to TradeLokal</h1>
      <p>Access your account to manage products and view your dashboard.</p>
    </div>
  </header>

  <main class="py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-sm rounded-4">
            <div class="card-body p-5">
              <h2 class="card-title mb-4">Login</h2>
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                      <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <form method="post" action="login.php">
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com"
                    value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>" required />
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" name="password" class="form-control" id="password"
                    placeholder="Enter your password" required />
                </div>
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-orange">Sign In</button>
                </div>
              </form>
              <div class="mt-4 text-center">
                <p class="mb-1">Don’t have an account yet?</p>
                <a href="register.php" class="text-decoration-none" style="color: var(--primary-green)">Create one
                  now</a>
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