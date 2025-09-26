<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";

$id = intval(get("id", 0));
if($id <= 0) json_err("Thiếu id");

$stmt = $mysqli->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
if(!$product) json_err("Không tìm thấy sản phẩm.", 404);

// images
$imgs = $mysqli->query("SELECT id, image_url, is_primary FROM product_images WHERE product_id=".$id." ORDER BY is_primary DESC, id ASC")->fetch_all(MYSQLI_ASSOC);

// category-specific details
$cat_name = strtolower($product["category_name"]);
$details = null;
switch($cat_name){
    case "laptop":
        $details = $mysqli->query("SELECT * FROM laptops WHERE product_id=".$id." LIMIT 1")->fetch_assoc();
        break;
    case "điện thoại":
    case "dien thoai":
    case "phone":
        $details = $mysqli->query("SELECT * FROM phones WHERE product_id=".$id." LIMIT 1")->fetch_assoc();
        break;
    case "linh kiện":
    case "linh kien":
    case "components":
        $details = $mysqli->query("SELECT * FROM components WHERE product_id=".$id." LIMIT 1")->fetch_assoc();
        break;
    default:
        $details = $mysqli->query("SELECT * FROM accessories WHERE product_id=".$id." LIMIT 1")->fetch_assoc();
        break;
}

// reviews
$reviews = $mysqli->query("SELECT r.id, r.rating, r.content, r.created_at, u.name as user_name FROM reviews r LEFT JOIN users u ON u.id=r.user_id WHERE r.product_id=".$id." ORDER BY r.created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);

// related
$related = $mysqli->query("SELECT p.id, p.name, p.price, (SELECT image_url FROM product_images WHERE product_id=p.id ORDER BY is_primary DESC, id ASC LIMIT 1) AS cover
FROM products p WHERE p.category_id=".$product["category_id"]." AND p.id<>".$id." ORDER BY RAND() LIMIT 8")->fetch_all(MYSQLI_ASSOC);

json_ok(["product"=>$product, "images"=>$imgs, "details"=>$details, "reviews"=>$reviews, "related"=>$related]);
?>