<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_admin($mysqli);
$id = intval(post("id")); $status = post("status","pending");
$allowed = ["pending","paid","shipped","completed","cancelled"];
if(!in_array($status,$allowed)) json_err("Trạng thái không hợp lệ.");
$stmt = $mysqli->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si",$status,$id);
if(!$stmt->execute()) json_err("Cập nhật thất bại: ".$stmt->error, 500);
json_ok([], "Đã cập nhật trạng thái.");
?>