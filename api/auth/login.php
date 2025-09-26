<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";

$email = strtolower(post("email"));
$pass = post("password");
if(!$email || !$pass) json_err("Thiếu thông tin.");

$stmt = $mysqli->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if(!$user) json_err("Email hoặc mật khẩu không đúng.", 401);
if(!password_verify($pass, $user["password_hash"])) json_err("Email hoặc mật khẩu không đúng.", 401);

$_SESSION["user_id"] = intval($user["id"]);
json_ok(["id"=>$user["id"], "name"=>$user["name"], "email"=>$user["email"], "role"=>$user["role"]], "Đăng nhập thành công.");
?>