<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";

$name = post("name");
$email = strtolower(post("email"));
$pass = post("password");

if(!$name || !$email || !$pass) json_err("Thiếu thông tin.");

$stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0) json_err("Email đã tồn tại.");
$stmt->close();

$hash = password_hash($pass, PASSWORD_BCRYPT);
$role = "user";
$stmt = $mysqli->prepare("INSERT INTO users(name,email,password_hash,role,created_at) VALUES(?,?,?,?,NOW())");
$stmt->bind_param("ssss", $name, $email, $hash, $role);
if(!$stmt->execute()) json_err("Đăng ký thất bại: ".$stmt->error, 500);

$_SESSION["user_id"] = $stmt->insert_id;
json_ok(["id"=>$stmt->insert_id, "name"=>$name, "email"=>$email, "role"=>$role], "Đăng ký thành công.");
?>