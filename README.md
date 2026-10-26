# 📚 ReadVerse — Nền tảng Thư viện & Đọc Sách Điện Tử Trực Tuyến (Laravel Framework)

Đồ án cuối môn học **Lập trình Mã nguồn mở** — Khoa Công nghệ Thông tin, Trường Đại học Công Thương TP.HCM (HUIT).

---

## 1. Công nghệ & Kiến trúc Kỹ thuật
- **Backend**: Laravel Framework, kiến trúc `Service - Repository Pattern`, bảo mật với `Policy`, `Form Request Validation` (thông báo lỗi tiếng Việt) và `Role-based Middleware`.
- **Cơ sở dữ liệu**: Hỗ trợ đầy đủ **MySQL** (`database/readverse_mysql.sql` + hệ thống 15 tệp Migration/Seeder) và **SQLite**.
- **Các tính năng Mở rộng (Điểm cộng Mục 5)**:
  1. **Xóa mềm & Thùng rác khôi phục (`SoftDeletes`)**: Lưu trữ an toàn sách đã xóa trong Thùng rác Admin, cho phép Khôi phục hoặc Xóa vĩnh viễn mà không làm hỏng lịch sử đơn hàng.
  2. **Tích hợp REST API bên ngoài (`OpenLibrary` & `Google Books API`)**: Tự động tra cứu thông tin sách theo tên/ISBN, điền Tiêu đề, Tác giả, Mô tả, Số trang và tải Ảnh bìa trực tiếp vào biểu mẫu.
  3. **Quản lý Đơn hàng, Biểu đồ Doanh thu (`Chart.js`) & Xuất báo cáo Excel/CSV**: Thống kê trực quan dòng tiền theo ngày/thể loại và xuất báo cáo `.csv` chuẩn UTF-8 BOM.
  4. **Giỏ hàng (`Shopping Cart`) & Thanh toán gộp qua VietQR**, **Pop-up Phản hồi Kiểm duyệt Bản thảo**, và **Trình đọc sách lật trang 3D (`PDF.js` + `StPageFlip`)**.

---

## 2. Hướng dẫn Cài đặt & Chạy Dự án
```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

- Truy cập ứng dụng tại: `http://127.0.0.1:8000`
- Tệp CSDL MySQL đính kèm sẵn tại: `database/readverse_mysql.sql`
