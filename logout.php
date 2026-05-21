<?php
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
logoutUser();
header('Location: login.php');
exit;
