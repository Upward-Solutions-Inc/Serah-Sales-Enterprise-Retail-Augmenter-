<?php
$start = microtime(true);
try {
    $pdo = new PDO("mysql:host=18.139.82.238;port=4503;dbname=u559597593_thenest", "root", "root");

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT 1");
    $stmt->fetch();
    echo "Connection successful in " . (microtime(true) - $start) . " seconds.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}