<?php
// db.php
// Update these values if your MySQL credentials are different.
$host = 'localhost';
$dbName = 'trade_lokal';
$user = 'root';
$password = '';

$mysqli = new mysqli($host, $user, $password, $dbName);
if ($mysqli->connect_errno) {
  http_response_code(500);
  die('Database connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function getDb()
{
  global $mysqli;
  return $mysqli;
}
