<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
$product_id = intval(post("product_id"));
if(isset($_SESSION["cart"][$product_id])) unset($_SESSION["cart"][$product_id]);
json_ok(["cart"=>$_SESSION["cart"] ?? []], "Đã xóa khỏi giỏ.");
?>