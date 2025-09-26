<?php
require_once __DIR__ . "/../config/config.php";
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["ok"=>false, "error"=>"DB connect failed: ".$mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");
session_start();
?>