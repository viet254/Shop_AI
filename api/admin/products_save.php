<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_admin($mysqli);

$id = intval(post("id", 0));
$name = post("name");
$category_id = intval(post("category_id"));
$price = floatval(post("price", 0));
$discount = floatval(post("discount", 0));
$stock = intval(post("stock", 0));
$status = post("status", "active");
$short_desc = post("short_desc", "");
$description = post("description", "");

// Hình ảnh (danh sách URL, phân tách bằng xuống dòng)
$images_raw = post("images", "");
$images = array_values(array_filter(array_map("trim", preg_split("/\r?\n/", $images_raw))));

if(!$name || $category_id<=0) json_err("Thiếu tên hoặc danh mục.");

if($id > 0){
    $stmt = $mysqli->prepare("UPDATE products SET name=?, category_id=?, price=?, discount=?, stock=?, status=?, short_desc=?, description=? WHERE id=?");
    $stmt->bind_param("sidddsssi", $name, $category_id, $price, $discount, $stock, $status, $short_desc, $description, $id);
    if(!$stmt->execute()) json_err("Cập nhật sản phẩm thất bại: ".$stmt->error, 500);
    $pid = $id;
    // reset images
    $mysqli->query("DELETE FROM product_images WHERE product_id=".$pid);
} else {
    $stmt = $mysqli->prepare("INSERT INTO products(name, category_id, price, discount, stock, status, short_desc, description, created_at) VALUES(?,?,?,?,?,?,?,?,NOW())");
    $stmt->bind_param("sidddsss", $name, $category_id, $price, $discount, $stock, $status, $short_desc, $description);
    if(!$stmt->execute()) json_err("Thêm sản phẩm thất bại: ".$stmt->error, 500);
    $pid = $stmt->insert_id;
}

// lưu images
$idx = 0;
foreach($images as $url){
    $is_primary = ($idx==0) ? 1 : 0;
    $stmt2 = $mysqli->prepare("INSERT INTO product_images(product_id, image_url, is_primary) VALUES(?,?,?)");
    $stmt2->bind_param("isi", $pid, $url, $is_primary);
    $stmt2->execute();
    $idx++;
}

// chi tiết theo loại (ẩn/hiện tuỳ danh mục)
$cat = $mysqli->query("SELECT name FROM categories WHERE id=".$category_id)->fetch_assoc();
$cname = strtolower($cat["name"] ?? "");

if($cname==="laptop"){
    $cpu = post("cpu",""); $ram = post("ram",""); $storage=post("storage",""); $gpu=post("gpu",""); $screen=post("screen",""); $weight=post("weight",""); $os=post("os","");
    if($id>0) $mysqli->query("DELETE FROM laptops WHERE product_id=".$pid);
    $stmt3 = $mysqli->prepare("INSERT INTO laptops(product_id,cpu,ram,storage,gpu,screen,weight,os) VALUES(?,?,?,?,?,?,?,?)");
    $stmt3->bind_param("isssssss", $pid,$cpu,$ram,$storage,$gpu,$screen,$weight,$os);
    $stmt3->execute();
} elseif(in_array($cname, ["điện thoại","dien thoai","phone"])) {
    $chip=post("chipset",""); $ram=post("ram",""); $storage=post("storage",""); $camera=post("camera",""); $battery=post("battery",""); $screen=post("screen",""); $sim=post("sim",""); $os=post("os","");
    if($id>0) $mysqli->query("DELETE FROM phones WHERE product_id=".$pid);
    $stmt3 = $mysqli->prepare("INSERT INTO phones(product_id,chipset,ram,storage,camera,battery,screen,sim,os) VALUES(?,?,?,?,?,?,?,?,?)");
    $stmt3->bind_param("issssssss", $pid,$chip,$ram,$storage,$camera,$battery,$screen,$sim,$os);
    $stmt3->execute();
} elseif(in_array($cname, ["linh kiện","linh kien","components"])) {
    $type=post("type",""); $brand=post("brand",""); $model=post("model",""); $specs=post("specs","");
    if($id>0) $mysqli->query("DELETE FROM components WHERE product_id=".$pid);
    $stmt3 = $mysqli->prepare("INSERT INTO components(product_id,type,brand,model,specs) VALUES(?,?,?,?,?)");
    $stmt3->bind_param("issss", $pid,$type,$brand,$model,$specs);
    $stmt3->execute();
} else {
    $type=post("type",""); $brand=post("brand",""); $compat=post("compatibility",""); $specs=post("specs","");
    if($id>0) $mysqli->query("DELETE FROM accessories WHERE product_id=".$pid);
    $stmt3 = $mysqli->prepare("INSERT INTO accessories(product_id,type,brand,compatibility,specs) VALUES(?,?,?,?,?)");
    $stmt3->bind_param("issss", $pid,$type,$brand,$compat,$specs);
    $stmt3->execute();
}

json_ok(["product_id"=>$pid], "Lưu sản phẩm thành công.");
?>