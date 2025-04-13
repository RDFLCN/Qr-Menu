<?php
require_once '../includes/db.php';
$pdo->query("UPDATE orders SET notified = 1 WHERE notified = 0");
