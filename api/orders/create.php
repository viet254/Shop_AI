<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_login();
if(empty($_SESSION["cart"])) json_err("Giỏ hàng trống.");

$uid = intval($_SESSION["user_id"]);
$shipping = post("shipping_address", "");
$note = post("note", "");
$method = post("payment_method", "COD");

$cart = $_SESSION["cart"];
$total = 0;
foreach($cart as $pid=>$qty){
    $res = $mysqli->query("SELECT price, stock FROM products WHERE id=".intval($pid));
    if($p = $res->fetch_assoc()){
        if($p["stock"] < $qty) json_err("Sản phẩm có số lượng không đủ.");
        $total += $p["price"] * $qty;
    }
}
$order_code = "OD".date("ymdHis").rand(100,999);
$stmt = $mysqli->prepare("INSERT INTO orders(user_id, order_code, status, total, shipping_address, payment_method, note, created_at) VALUES(?, ?, 'pending', ?, ?, ?, ?, NOW())");
$stmt->bind_param("isdsss", $uid, $order_code, $total, $shipping, $method, $note);
if(!$stmt->execute()) json_err("Tạo đơn thất bại: ".$stmt->error, 500);
$order_id = $stmt->insert_id;

foreach($cart as $pid=>$qty){
    $pid = intval($pid);
    $r = $mysqli->query("SELECT price FROM products WHERE id=$pid")->fetch_assoc();
    $price = $r ? floatval($r["price"]) : 0;
    $stmt2 = $mysqli->prepare("INSERT INTO order_items(order_id, product_id, qty, price) VALUES(?,?,?,?)");
    $stmt2->bind_param("iiid", $order_id, $pid, $qty, $price);
    $stmt2->execute();
    $mysqli->query("UPDATE products SET stock = stock - $qty WHERE id=$pid");
}
$_SESSION["cart"] = [];

json_ok(["order_id"=>$order_id, "order_code"=>$order_code], "Đã tạo đơn hàng.");
?>