<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";

if(!isset($_SESSION["user_id"])) json_ok(null);
$uid = intval($_SESSION["user_id"]);
$stmt = $mysqli->prepare("SELECT id, name, email, role, phone, address FROM users WHERE id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
json_ok($user);
?>