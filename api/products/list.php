<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";

$page = max(1, intval(get("page", 1)));
$per = max(1, min(24, intval(get("per", 12))));
$offset = ($page-1) * $per;
$cat_id = intval(get("category_id", 0));
$search = get("q", "");

$where = " WHERE p.status='active' ";
$params = [];
$types = "";

// category filter
if($cat_id > 0){
    $where .= " AND p.category_id=? ";
    $types .= "i";
    $params[] = $cat_id;
}

// search filter
if($search !== ""){
    $where .= " AND (p.name LIKE CONCAT('%',?,'%') OR p.short_desc LIKE CONCAT('%',?,'%')) ";
    $types .= "ss";
    $params[] = $search; $params[] = $search;
}

$sql = "SELECT p.id, p.name, p.price, p.discount, p.short_desc, p.category_id, c.name as category_name,
        (SELECT image_url FROM product_images WHERE product_id=p.id ORDER BY is_primary DESC, id ASC LIMIT 1) AS cover
        FROM products p
        LEFT JOIN categories c ON c.id=p.category_id
        $where
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";

$types .= "ii";
$params[] = $per; $params[] = $offset;

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);

// total
$count_sql = "SELECT COUNT(*) AS cnt FROM products p " . $where;
$stmt2 = $mysqli->prepare($count_sql);
if(strlen($types)-2 > 0){
    // drop last "ii" used for limit/offset
    $types_no_limit = substr($types, 0, -2);
    $bind_params = array_slice($params, 0, -2);
    $stmt2->bind_param($types_no_limit, ...$bind_params);
}
$stmt2->execute();
$total = $stmt2->get_result()->fetch_assoc()["cnt"] ?? 0;

json_ok(["items"=>$rows, "page"=>$page, "per"=>$per, "total"=>intval($total)]);
?>