<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_login();

$product_id = intval(post("product_id"));
$rating = intval(post("rating"));
$content = post("content", "");

if($product_id<=0 || $rating<1 || $rating>5) json_err("Dữ liệu không hợp lệ.");
$uid = intval($_SESSION["user_id"]);
$stmt = $mysqli->prepare("INSERT INTO reviews(product_id, user_id, rating, content, created_at) VALUES(?,?,?,?,NOW())");
$stmt->bind_param("iiis", $product_id, $uid, $rating, $content);
if(!$stmt->execute()) json_err("Không thể thêm đánh giá: ".$stmt->error, 500);
json_ok([], "Đã gửi đánh giá.");
?>