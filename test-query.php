<?php

require_once 'db.php';

$stmt = $pdo->query("SHOW TABLES");

$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<pre>";
print_r($tables);
echo "</pre>";