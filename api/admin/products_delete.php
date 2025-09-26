<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_admin($mysqli);

$id = intval(post("id", 0));
if($id<=0) json_err("Thiếu id");
$mysqli->query("DELETE FROM product_images WHERE product_id=".$id);
$mysqli->query("DELETE FROM laptops WHERE product_id=".$id);
$mysqli->query("DELETE FROM phones WHERE product_id=".$id);
$mysqli->query("DELETE FROM components WHERE product_id=".$id);
$mysqli->query("DELETE FROM accessories WHERE product_id=".$id);
$mysqli->query("DELETE FROM products WHERE id=".$id);
json_ok([], "Đã xóa.");
?>