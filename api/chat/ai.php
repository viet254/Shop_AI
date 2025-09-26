<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../utils.php";
require_once __DIR__ . "/../..//config/config.php";

$query = post("q", "");
if(!$query) json_err("Thiếu câu hỏi.");

// Nếu có API KEY -> gọi OpenAI (proxy)
if(defined("OPENAI_API_KEY") && OPENAI_API_KEY){
    $payload = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role"=>"system","content"=>"Bạn là trợ lý bán hàng cho website TechShop Blue. Hãy gợi ý sản phẩm dựa trên từ khóa người dùng, đưa 3-5 gợi ý ngắn gọn."],
            ["role"=>"user","content"=>$query]
        ],
        "temperature" => 0.4
    ];
    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer ".OPENAI_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    if($resp===false){
        // fallback
    } else {
        $json = json_decode($resp, true);
        if(isset($json["choices"][0]["message"]["content"])){
            json_ok(["answer"=>$json["choices"][0]["message"]["content"]]);
        }
    }
}

// Fallback rule-based: tìm nhanh 5 sản phẩm theo từ khóa
$q = $mysqli->real_escape_string($query);
$res = $mysqli->query("SELECT id, name, price, (SELECT image_url FROM product_images WHERE product_id=p.id ORDER BY is_primary DESC LIMIT 1) cover
FROM products p WHERE name LIKE '%$q%' OR short_desc LIKE '%$q%' ORDER BY created_at DESC LIMIT 5");
$items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
if($items){
    json_ok(["suggestions"=>$items, "answer"=>"Mình gợi ý vài sản phẩm theo từ khóa của bạn:"]);
} else {
    json_ok(["suggestions"=>[], "answer"=>"Mình chưa tìm thấy sản phẩm phù hợp. Bạn thử mô tả rõ hơn nhu cầu (ngân sách, mục đích dùng, kích cỡ, v.v.) nhé!"]);
}
?>