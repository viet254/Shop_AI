<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
session_destroy();
json_ok([], "Đã đăng xuất.");
?>