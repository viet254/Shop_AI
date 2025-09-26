<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_login();

$name = post("name");
$phone = post("phone");
$address = post("address");

$uid = intval($_SESSION["user_id"]);
$stmt = $mysqli->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
$stmt->bind_param("sssi", $name, $phone, $address, $uid);
if(!$stmt->execute()) json_err("Cập nhật thất bại: ".$stmt->error, 500);

json_ok([], "Cập nhật thông tin thành công.");
?>