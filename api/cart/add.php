<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";

$product_id = intval(post("product_id"));
$qty = max(1, intval(post("qty", 1)));
if($product_id<=0) json_err("Thiếu product_id");

if(!isset($_SESSION["cart"])) $_SESSION["cart"] = [];
if(!isset($_SESSION["cart"][$product_id])) $_SESSION["cart"][$product_id] = 0;
$_SESSION["cart"][$product_id] += $qty;

json_ok(["cart"=>$_SESSION["cart"]], "Đã thêm vào giỏ.");
?>