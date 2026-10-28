# ReadVerse — Nền tảng Thư viện & Đọc Sách Điện Tử Trực Tuyến (Laravel Framework)

Đồ án cuối môn học **Lập trình Mã nguồn mở** — Khoa Công nghệ Thông tin, Trường Đại học Công Thương TP.HCM (HUIT).

---

## 1. Công nghệ & Kiến trúc Kỹ thuật
- **Backend**: Laravel Framework (PHP 8.2+), kiến trúc `Service - Repository Pattern`, bảo mật với `Policy`, `Form Request Validation` (thông báo lỗi tiếng Việt) và `Role-based Middleware`.
- **Cơ sở dữ liệu**: Hỗ trợ đầy đủ **MySQL** (`database/readverse_mysql.sql` kèm lệnh tự động khởi tạo `php artisan db:setup-mysql`, hệ thống 15 tệp Migration/Seeder) và **SQLite** (`database/database.sqlite`).
- **Các tính năng Mở rộng (Điểm cộng Mục 5)**:
  1. **Xóa mềm & Thùng rác khôi phục (`SoftDeletes`)**: Lưu trữ an toàn sách đã xóa trong Thùng rác Admin (`/admin/ebooks?status=trashed`), cho phép Khôi phục hoặc Xóa vĩnh viễn mà không làm hỏng lịch sử đơn hàng.
  2. **Tích hợp REST API bên ngoài (`OpenLibrary` & `Google Books API`)**: Tự động tra cứu thông tin sách theo tên hoặc mã ISBN tại trang đăng sách (`/author/ebooks/create`), tự động điền Tiêu đề, Tác giả, Mô tả, Số trang và tải Ảnh bìa trực tiếp vào biểu mẫu.
  3. **Quản lý Đơn hàng, Biểu đồ Doanh thu (`Chart.js`) & Xuất báo cáo Excel/CSV**: Thống kê trực quan dòng tiền theo ngày/thể loại tại `/admin/orders` và xuất báo cáo `.csv` chuẩn UTF-8 BOM mở trực tiếp trên Microsoft Excel.
  4. **Giỏ hàng (`Shopping Cart`) & Thanh toán gộp qua VietQR (`/cart`)**, **Pop-up Phản hồi Kiểm duyệt Bản thảo**, **Hồ sơ cá nhân (`/profile`)**, và **Trình đọc sách lật trang 3D (`PDF.js` + `StPageFlip`)**.

---

## 2. Hướng dẫn Cài đặt & Chạy Dự án (Windows Laragon/XAMPP & macOS)

> **Yêu cầu trước khi chạy:** Hãy bật dịch vụ **MySQL** trong **Laragon** hoặc **XAMPP** (mặc định cổng `3306`, user `root`, password để trống).

### Trường hợp 1: Máy mới bắt đầu Clone dự án về lần đầu
Mở Terminal (PowerShell, CMD hoặc Git Bash) và chạy lần lượt các lệnh sau:

```bash
git clone https://github.com/Kangghangg/ReadVerse.git
cd ReadVerse
composer install
php -r "copy('.env.example', '.env');"
php artisan key:generate
php artisan db:setup-mysql
php -r "if (is_link('public/storage') || is_file('public/storage')) { @unlink('public/storage'); } elseif (is_dir('public/storage')) { @rmdir('public/storage'); }"
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

### Trường hợp 2: Máy đã Clone từ trước và muốn kéo bản cập nhật mới nhất về
Đứng tại thư mục `ReadVerse` và chạy:

```bash
git fetch origin main
git reset --hard origin/main
composer dump-autoload
php -r "copy('.env.example', '.env');"
php artisan key:generate
php artisan db:setup-mysql
php -r "if (is_link('public/storage') || is_file('public/storage')) { @unlink('public/storage'); } elseif (is_dir('public/storage')) { @rmdir('public/storage'); }"
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

- Sau khi chạy xong, truy cập ứng dụng tại: **`http://127.0.0.1:8000`**
- **Ghi chú về CSDL MySQL**: Lệnh `php artisan db:setup-mysql` ở trên sẽ **tự động tạo database `readverse`** trong MySQL và **tự động nạp toàn bộ 15 bảng + 67 đầu sách + tài khoản** từ tệp `database/readverse_mysql.sql` (không cần mở phpMyAdmin import thủ công).
- Nếu muốn chạy bằng **SQLite** (không cần bật MySQL): Mở file `.env`, sửa dòng `DB_CONNECTION=mysql` thành `DB_CONNECTION=sqlite` rồi chạy `php artisan optimize:clear && php artisan serve`.

---

## 3. Danh sách Tài khoản Đăng nhập Có sẵn

| Vai trò (Role) | Email đăng nhập | Mật khẩu | Quyền hạn chính |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin 1)** | `admin@readverse.com` | `admin123` | Toàn quyền quản trị `/admin/*`, duyệt/từ chối sách, thùng rác, thống kê doanh thu |
| **Quản trị viên (Admin 2)** | `admin@readverse.vn` | `Admin@123456` | Tài khoản Quản trị viên dự phòng |
| **Tác giả (Author - Nhóm 3)** | `nhom3@readverse.com` | `nhom3read` | Cổng tác giả `/author/ebooks`, đăng sách PDF, tra cứu API, hồ sơ cá nhân |
| **Tác giả (Author 2)** | `nguyennhatanh@readverse.vn` | `Author@123456` | Tài khoản Tác giả dự phòng |
| **Độc giả (Reader)** | `ledubaokhang@gmail.com` | `User@123456` | Đọc sách 3D, giỏ hàng & thanh toán VietQR, tủ sách cá nhân, đánh giá sao |
| **Độc giả (Reader 2)** | `minhtam.dev@gmail.com` | `User@123456` | Tài khoản Độc giả dự phòng |
