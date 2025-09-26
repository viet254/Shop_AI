<?php
// TechShop Blue - cấu hình kết nối XAMPP MySQL
// Hãy chỉnh lại theo XAMPP của bạn (mặc định: root / mật khẩu rỗng)
define("DB_HOST", "127.0.0.1");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "techshop_blue");
define("BASE_PATH", "/techshop-blue"); // đường dẫn dự án trong htdocs (ví dụ: /techshop-blue)

// (Tùy chọn) Tích hợp AI Chat: đặt API key ở đây nếu bạn muốn dùng OpenAI qua proxy PHP
// Nếu để trống, hệ thống sẽ dùng chế độ gợi ý rule-based đơn giản.
define("OPENAI_API_KEY", ""); // ví dụ: "sk-..."
?>