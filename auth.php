<?php
// auth.php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function getDbConnection()
{
  return getDb();
}

function isLoggedIn()
{
  return !empty($_SESSION['user']);
}

function currentUser()
{
  return $_SESSION['user'] ?? null;
}

function requireLogin()
{
  if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
  }
}

function isSeller()
{
  $user = currentUser();
  return isset($user['role']) && $user['role'] === 'seller';
}

function requireSeller()
{
  requireLogin();
  if (!isSeller()) {
    header('Location: dashboard.php');
    exit;
  }
}

function loginUser(string $email, string $password): bool
{
  $mysqli = getDbConnection();
  $noRoleColumn = false;
  try {
    $stmt = $mysqli->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
  } catch (mysqli_sql_exception $e) {
    // fallback when `role` column doesn't exist yet
    $noRoleColumn = true;
    $stmt = $mysqli->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
  }
  if (!$stmt) {
    return false;
  }
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if (!$user) {
    return false;
  }

  if (password_verify($password, $user['password_hash'])) {
    unset($user['password_hash']);
    if (!isset($user['role']) || $noRoleColumn) {
      $user['role'] = 'customer';
    }
    // FIX: Set both session variables so the rest of the application works seamlessly
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
    return true;
  }

  return false;
}

function registerUser(string $name, string $email, string $password, string $role = 'customer'): bool
{
  $allowedRoles = ['customer', 'seller'];
  if (!in_array($role, $allowedRoles, true)) {
    $role = 'customer';
  }

  $mysqli = getDbConnection();
  $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  if (!$stmt) {
    return false;
  }
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->close();
    return false;
  }
  $stmt->close();

  $passwordHash = password_hash($password, PASSWORD_DEFAULT);
  // Try to insert with role column; if DB doesn't have role, fall back
  try {
    $insert = $mysqli->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    if ($insert) {
      $insert->bind_param('ssss', $name, $email, $passwordHash, $role);
      $success = $insert->execute();
      $insert->close();
      return (bool) $success;
    }
  } catch (mysqli_sql_exception $e) {
    // ignore and try without role
  }

  $insert = $mysqli->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
  if (!$insert) {
    return false;
  }
  $insert->bind_param('sss', $name, $email, $passwordHash);
  $success = $insert->execute();
  $insert->close();

  return (bool) $success;

  return $success;
}

function logoutUser()
{
  if (session_status() !== PHP_SESSION_NONE) {
    session_unset();
    session_destroy();
  }
}
