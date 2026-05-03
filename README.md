# Autoship Driver 🚚

**Autoship Driver** là một hệ thống web application được phát triển bằng Laravel, thiết kế chuyên biệt để quản lý luồng giao nhận hàng hóa. Hệ thống cung cấp giải pháp toàn diện để kết nối và quản lý 3 đối tượng chính: **Quản trị viên (Admin)**, **Tài xế (Driver)**, và **Cửa hàng (Shop)**.

## 🌟 Các tính năng nổi bật

Dự án được chia thành các phân hệ chính với hệ thống phân quyền (Role-based) chặt chẽ:

* **👨‍💼 Phân hệ Quản trị viên (Admin):**
    * Dashboard thống kê tổng quan hệ thống.
    * Quản lý Người dùng, Cửa hàng, và Tài xế.
    * Quản lý và điều phối Đơn hàng.
    * Theo dõi Lộ trình (Map Routing) theo thời gian thực.
    * Quản lý cấu hình phí hệ thống (System Fees).
    * Đối soát tài chính (Settlement) với Cửa hàng và Tài xế.
* **🛵 Phân hệ Tài xế (Driver):**
    * Nhận và quản lý lộ trình giao hàng trong ngày.
    * Cập nhật trạng thái đơn hàng (Đã giao, hoàn trả,...).
    * Quản lý ví cá nhân (Wallet), theo dõi thu nhập và KPI.
* **🏪 Phân hệ Cửa hàng (Shop):**
    * Tạo và quản lý danh sách đơn hàng cần giao.
    * Theo dõi tình trạng vận chuyển của đơn hàng.
    * Quản lý tài chính, đối soát cước phí với hệ thống.

## 🛠 Công nghệ sử dụng

* **Backend:** PHP (Framework Laravel)
* **Frontend:** Blade Template, TailwindCSS, Vite, JavaScript
* **Database:** MySQL / MariaDB
* **Bản đồ & Routing:** LeafletJS / Map APIs (Tùy cấu hình)

---

## 🚀 Hướng dẫn cài đặt và chạy dự án (Dành cho Developer)

Để chạy dự án này trên máy local của bạn, vui lòng thực hiện tuần tự 8 bước dưới đây.

### Yêu cầu hệ thống (Prerequisites)
Đảm bảo máy tính của bạn đã cài đặt sẵn các phần mềm sau:
* [PHP](https://www.php.net/) >= 8.2
* [Composer](https://getcomposer.org/) (Trình quản lý thư viện PHP)
* [Node.js & NPM](https://nodejs.org/) (Để build frontend tĩnh)
* [MySQL](https://www.mysql.com/) (thông qua XAMPP, Laragon, hoặc cài trực tiếp)

---

### Bước 1: Clone dự án
Mở Terminal/Command Prompt và chạy lệnh để tải mã nguồn về máy:
```bash
git clone [https://github.com/PhucVH2004/autoship-driver.git](https://github.com/PhucVH2004/autoship-driver.git)
cd autoship-driver


Bước 2: Cài đặt thư viện Backend (PHP)
Chạy lệnh composer để tải các thư viện cần thiết cho Laravel:
composer install

Bước 3: Cài đặt thư viện Frontend (Node.js)
Cài đặt và build các file CSS/JS thông qua Vite:
npm install
npm run build

Bước 4: Cấu hình Môi trường (.env)
Tạo file cấu hình môi trường bằng cách copy từ file mẫu:

cp .env.example .env

Mở file .env vừa tạo bằng trình soạn thảo code (VS Code, PHPStorm,...) và điền thông tin kết nối Database của bạn:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autoship_db    # Đổi thành tên database bạn đã tạo trong MySQL
DB_USERNAME=root           # Tài khoản MySQL của bạn (thường XAMPP là root)
DB_PASSWORD=               # Mật khẩu MySQL của bạn (thường XAMPP để trống)


(Hãy chắc chắn rằng bạn đã mở MySQL và tạo một database trống có tên trùng với DB_DATABASE ở trên).

Bước 5: Tạo App Key
Generate Application Key cho Laravel (dùng để mã hóa bảo mật dữ liệu):
php artisan key:generate

Bước 6: Chạy Migration và Seed dữ liệu giả
php artisan migrate --seed
Bước 7: Link Storage (Quản lý file upload)
php artisan storage:link

Bước 8: Khởi chạy Server
php artisan serve
