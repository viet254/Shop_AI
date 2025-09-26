# TechShop Blue

Dự án mẫu website bán hàng (Laptop, Điện thoại, Linh kiện, Phụ kiện) chạy trên **XAMPP** với PHP/MySQL, giao diện xanh biển - trắng, có **chatbox AI** tư vấn (proxy đến OpenAI hoặc fallback rule-based).

## Hướng dẫn cài nhanh

1. Mở **XAMPP** → Start Apache + MySQL.
2. Tạo DB và bảng:
   - Mở **phpMyAdmin** → Import file `sql/schema.sql`.
3. Copy thư mục **techshop-blue** vào `htdocs` (ví dụ `C:/xampp/htdocs/Shop_AI`).
4. Mở trình duyệt: `http://localhost/techshop-blue/public/index.html`.
5. Đăng nhập Admin: email `admin@techshop.local`, mật khẩu `admin123` (có sẵn trong seed).
   - Đăng nhập tại `http://localhost/techshop-blue/public/login.html` → sẽ chuyển sang trang quản trị.

> Nếu bạn đặt thư mục khác tên, hãy sửa `BASE_PATH` trong `config/config.php` cho đúng.

### Chat AI
- Mặc định chat sẽ gợi ý theo từ khóa. Nếu có **OpenAI API Key**, sửa trong `config/config.php`:
  ```php
  define("OPENAI_API_KEY", "sk-..."); 
  ```
  Sau đó chat sẽ gọi model `gpt-4o-mini` qua endpoint proxy PHP `api/chat/ai.php`.

## Tính năng chính
- Trang chủ, tìm kiếm, danh mục, chi tiết sản phẩm (nhiều ảnh), đánh giá, sản phẩm liên quan.
- Giỏ hàng (session), tạo đơn (COD).
- Tài khoản người dùng: đăng ký/đăng nhập, xem & chỉnh sửa thông tin.
- Admin: thêm/sửa/xóa sản phẩm (kèm ảnh URL), đơn hàng (xem & đổi trạng thái).
- Phân loại 4 nhóm: Laptop / Điện thoại / Linh kiện / Phụ kiện với **bảng chi tiết riêng** cho từng loại.
- Kiến trúc tách **giao diện / CSS / JS / API / SQL** để dễ bảo trì, mở rộng.

## Khắc phục lỗi "mạng" khi đăng nhập/đăng ký
- **QUAN TRỌNG**: Các trang front-end phải chạy qua Apache (http://localhost/...), không mở file `index.html` trực tiếp bằng `file://...`.
- Đảm bảo đường dẫn **BASE** trong JS tự nhận đúng khi bạn đặt vào `/techshop-blue`. Nếu bạn đổi thư mục:
  - Sửa `define("BASE_PATH", "/ten-thu-muc-cua-ban")` trong `config/config.php`.
  - Nếu cần, trong `public/assets/js/app.js` bạn có thể set `const BASE = "/ten-thu-muc-cua-ban";`.

## Mở rộng
- Thêm thanh toán online, mã giảm giá, wishlist UI, bộ lọc nâng cao, phân trang.
- Upload ảnh qua PHP thay vì nhập URL.
- Tối ưu SEO, sitemap, bộ lọc điều kiện giống CellphoneS/Laptop88 (giá, hãng, RAM, SSD...).
- Thêm log activity admin, dashboard thống kê.

