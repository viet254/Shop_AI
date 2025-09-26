<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_admin($mysqli);
$res = $mysqli->query("SELECT o.id, o.order_code, o.status, o.total, o.created_at, u.name as customer FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 100");
$rows = $res->fetch_all(MYSQLI_ASSOC);
json_ok($rows);
?>