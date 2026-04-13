# Finance Laravel API

Hướng dẫn cài đặt và chạy project backend `finance.laravel`.

## 1) Yêu cầu môi trường

- PHP `^8.3`
- Composer
- Node.js + npm
- MySQL 8+
- Extension PHP `pdo_mysql`

## 2) Cài đặt nhanh

```bash
composer setup
```

Script `setup` trong `composer.json` sẽ chạy:
- `composer install`
- tạo `.env` từ `.env.example` (nếu chưa có)
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install --ignore-scripts`
- `npm run build`

## 3) Cài đặt thủ công (khuyến nghị khi cần tùy chỉnh)

### Bước 1: Cài dependency

```bash
composer install
npm install
```

### Bước 2: Tạo file môi trường

```bash
cp .env.example .env
```

### Bước 3: Cấu hình `.env`

Các biến quan trọng:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance
DB_USERNAME=root
DB_PASSWORD=your_password

JWT_ISSUER=CREASOFT
JWT_AUDIENCE=Client
JWT_SIGNATURE=your_secret_key
JWT_LEEWAY=0
```

### Bước 4: Tạo app key

```bash
php artisan key:generate
```

### Bước 5: Migrate + seed dữ liệu

```bash
php artisan migrate
php artisan db:seed
```

Seeder mặc định trong `database/seeders/DatabaseSeeder.php`:
- `RolesSeeder`
- `PermissionsSeeder`
- `AdminPersonSeeder`

### Bước 6: Chạy ứng dụng

```bash
php artisan serve
```

API mặc định: `http://127.0.0.1:8000`

## 4) Chạy ở chế độ dev đầy đủ

```bash
composer run dev
```

Lệnh này chạy đồng thời:
- Laravel server
- queue listener
- log tail (`pail`)
- Vite dev server

## 5) Chạy test

```bash
php artisan test
```

## 6) Một số endpoint cơ bản

- `POST /api/authentications/login`
- `GET /api/authentications/me` (cần Bearer token)
- `POST /api/authentications/forgot-pasword`
- `GET /api/health`

## 7) Lỗi thường gặp

### Không kết nối được MySQL
- Kiểm tra `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Kiểm tra extension `pdo_mysql` đã bật trong PHP
- Đảm bảo user MySQL có quyền trên database `finance`

### JWT lỗi chữ ký
- Đảm bảo `JWT_SIGNATURE` có giá trị và đồng nhất giữa môi trường chạy API và client
