<?php
function json_ok($data=[], $msg=""){
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>true, "data"=>$data, "message"=>$msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function json_err($msg="Lỗi không xác định", $code=400){
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>false, "error"=>$msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function require_login(){
    if(!isset($_SESSION["user_id"])) json_err("Bạn cần đăng nhập.", 401);
}
function require_admin($mysqli){
    if(!isset($_SESSION["user_id"])) json_err("Bạn cần đăng nhập.", 401);
    $uid = intval($_SESSION["user_id"]);
    $rs = $mysqli->query("SELECT role FROM users WHERE id=$uid");
    if(!$rs) json_err("Không kiểm tra được quyền.");
    $row = $rs->fetch_assoc();
    if(!$row || $row["role"]!=="admin") json_err("Bạn không có quyền admin.", 403);
}
function post($key, $default=null){
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}
function get($key, $default=null){
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}
function hash_pass($pwd){
    return password_hash($pwd, PASSWORD_BCRYPT);
}
function verify_pass($pwd, $hash){
    return password_verify($pwd, $hash);
}
?>