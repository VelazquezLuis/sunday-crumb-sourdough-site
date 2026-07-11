<?php
$host = 'localhost';
$dbname = 'u577510376_Eventos';
$username = 'u577510376_Jocelyn';
$password = 'Chilango$14';

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
  die('Database connection failed.');
}
?>