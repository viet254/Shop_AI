<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
if(!isset($_SESSION["cart"])) $_SESSION["cart"] = [];
$cart = $_SESSION["cart"];
$items = [];
$total = 0;

foreach($cart as $pid=>$qty){
    $pid = intval($pid);
    $res = $mysqli->query("SELECT id, name, price FROM products WHERE id=$pid");
    if($row = $res->fetch_assoc()){
        $row["qty"] = $qty;
        $row["subtotal"] = $row["price"] * $qty;
        $items[] = $row;
        $total += $row["subtotal"];
    }
}
json_ok(["items"=>$items, "total"=>$total]);
?>