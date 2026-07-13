<?php

require_once '../config.php';

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS
);


try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

} catch (PDOException $e) {
  die('Database connection failed: ' . $e->getMessage());
}

?>