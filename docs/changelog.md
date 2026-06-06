# Changelog - Sistem Order

## 2026-06-06

### Fixed
- **cPanel 500 Error - PHP 7.x Compatibility:** Fixed multiple PHP 8+ syntax causing fatal errors on cPanel hosts running PHP 7.2-7.3:
  - Removed typed class properties (`private PDO $db`) from all 7 model files
  - Replaced `match()` expressions with `switch`/array lookup in AuthController and SalesModel
  - Removed union return types (`int|false`, `array|false`) from 20 functions in 5 model files
  - Changed `session_set_cookie_params()` from array syntax (PHP 7.3+) to parameter-based syntax
  - Removed typed static property (`private static string $logDir`) from Logger.php
- **cPanel 500 Error - Apache/htaccess:** Added `IfModule mod_rewrite.c` wrapper and `Options` directives to both `.htaccess` files to prevent 500 errors when mod_rewrite is not loaded or configured differently
- **cPanel 500 Error - Security Headers:** Added `headers_sent()` guard to `setSecurityHeaders()`, added `unsafe-eval` and `blob:` to CSP for better compatibility, added `connect-src` directive
- **cPanel 500 Error - Error Handling:** Added try/catch with graceful error display at both entry points (`index.php` and `public/index.php`), added PHP version check (7.2+ required)

## 2026-03-22

### Penambahbaikan Modul Admin, Cashier & Resit
- **Paparan Kad Admin Mudah Alih:** Susun atur kad statistik (dashboard) kini dipaparkan dalam format 2 lajur (*2-columns grid*) secara responsif pada skrin mudah alih untuk penjimatan ruang.
- **Akaun Kumpulan Cashier:** Peranan pengguna baharu (`cashier`) telah ditambah di dalam sistem, membolehkan pentadbir mengurus status pesanan dan jualan di kaunter secara khusus.
- **Pengesahan Manual Bayaran Tunai:** Transaksi pembayaran atas kaunter (tunai) tidak lagi diklasifikasikan sebagai '*berjaya*' secara automatik. Pesanan akan berada pada tahap menunggu sehingga pengesahan rasmi pihak *cashier*.
- **Modul Cetakan & Muat Turun Resit:** Pelanggan di ruangan status boleh memuat turun dan mencetak resit rasmi (format printer termal) secara langsung selepas status carian berpihak kepada selesaian perkhidmatan (*completed*).
- **Paparan Papan Pemuka Cashier Lanjutan:** Halaman *Buat Order* (`staff-order.php`) telah dinaik taraf menjadi sebuah *sistem Point-of-Sale (POS)* menyeluruh yang mampu mengawal selia daftar senarai tab pesanan dan menerima tempahan tunai secara sebelah-menyebelah menerusi fungsi pelipat borang (*collapsible form*).
- **Konfigurasi No Meja Fleksibel:** Menambah kebolehlaksanaan pilihan `order_allow_table` dalam konfigurasi (pangkalan data) untuk mengawal pemaparan ruangan "No. Meja" pada dompet Troli awam dan sistem POS rasmi.
- **Pilihan Status Bayaran Semasa Pembelian:** Penambahan status bayaran pra-tetap dalam borang pesanan kakitangan membolehkan pengecaman bil/status Tunai dan QR secara manual. Sistem bil tidak lagi memaksa status automatik sebaliknya membenarkan kakitangan menjana rekod *Telah Dibayar* atau *Belum Dibayar*.
- **Posisi Logo Utama Diratakan Kiri:** Penetapan susunan logo navigasi atas/header kepada struktur perataan kiri untuk semua dimensi skrin.
- **Kad Statistik Meleret (Slider) Universal:** Mengubah grid kotak statistik ringkasan papan pemuka Jualan kepada struktur jujukan meleret (UI Slider) untuk **semua saiz dimensi pelayar**, sama ada desktop, tab (tablet), mahupun telefon pintar. Ia membenarkan pengguna menatal kad statistik ke kiri dan kanan dengan lancar.
- **Pembaikan Carta Trend Jualan:** Memperbaiki logik paparan carta graf garis pada "Dashboard Jualan" untuk menampung data tanpa nil (0 jualan) dengan meratakan hari demi hari agar graf tidak senget dan tepat, serta membaiki dimensi responsif pada skrin mudah alih.

### Pengubahsuaian Reka Bentuk *Bottom Menu* & Format Nombor Pesanan
- **Menu Navigasi Sentuhan Aplikasi (Tab Bar):** Reka bentuk *Floating Bottom Navigation* dinaik taraf menjadi reka bentuk *tab bar* mudah alih (*mobile app style*). Panel navigasi kini merangkumi 100% lebar di pangkal bawah skrin berserta *safe-area inset* agar ia tidak menghalang kandungan visual.
- **Pengoptimuman Ketinggian Desktop:** Mengurangkan ketinggian dan jarak kelegaan (*padding gap*) menu terapung secara responsif apabila dibuka di pelayar skrin besar (*desktop*), supaya ia nampak lebih kemas dan tidak memakan terlalu banyak ruang.
- **Kategori Menu Lengkit (*Sticky Category Tabs*):** Ruang saringan kategori menu (`Semua`, `Minuman`, dll) pada bahagian atas carian (di dalam `menu.php`) kini berfungsi secara *sticky* (sentiasa lekang) pada kedudukan betul-betul di bawah logo/navigasi utama apabila di-skrol ke bawah. Pengguna kini boleh bertukar kategori pada bila-bila masa tanpa perlu *scroll* semula ke puncak.
- **Format Nombor Pesanan Baharu:** Struktur nombor siri pesanan (`Security::generateOrderNumber`) kini dipermudahkan kepada format berasaskan tarikh berserta kod 4 digit (Contoh: `220326-1234`). Ini menjadikannya lebih mesra dan senang dirujuk berbanding struktur rawak yang panjang sebelum ini.
- **Butang Tatal Pantas (FAB):** Pengguna kini boleh menatal ke bahagian atas atau bawah pesanan menggunakan bebenang aksi terapung (*floating action buttons*) di penjuru skrin, yang kini digabungkan mesra ke dalam bar navigasi untuk penjimatan ruang.

### Reka Bentuk Semula Antaramuka Status Pesanan (Mobile-First)
- **Kemas kini Antaramuka:** Antaramuka penjejakan pesanan di `views/order-status.php` telah direka bentuk semula secara *mobile-first*. Ciri terbaru merangkumi struktur garis masa (*timeline*) menegak baharu dengan kesan animasi *pulse* dan elemen reka bentuk *glassmorphism*.
- **Sistem Notifikasi Push (Desktop/Mobile):** Penambahan fungsi notifikasi antaramuka secara masa-nyata (*frontend toast alert*) dan notifikasi peringatan peringkat sistem OS (HTML5 *Push Notifications API*). Pengguna akan dimaklumkan secara automatik (tanpa me-refresh) jika status pesanan mereka berubah.


### Pembaikan URL Aplikasi
- **Penyelesaian `APP_URL`:** URL asas aplikasi (`APP_URL`) kini dijana secara dinamik berdasarkan `$_SERVER['HTTP_HOST']` dan parameter pelayan, tidak lagi statik (`.config.php`). Ini membolehkan sistem diakses melaui mana-mana domain dengan mudah.
- **Kemas Kini Skema Pangkalan Data:** Laras tetapan `app_url` telah dibuang dari senarai `system_settings` awal (`database.sql`) kerana ia sudah ditentukan dalam tetapan konfigurasi fail.

### Pembangunan Awal Sistem Order

**Penerangan:** Pembinaan lengkap sistem pesanan makanan dalam talian (Sistem Order) termasuk semua modul backend dan frontend.

**Komponen yang dibina:**

#### Pangkalan Data
- Cipta skema lengkap dengan 11 jadual: `users`, `categories`, `menu_items`, `orders`, `order_items`, `payments`, `ingredients`, `grocery_lists`, `grocery_items`, `activity_logs`
- Masukkan data awal untuk admin, kategori, dan contoh menu

```sql
-- Rujuk database.sql untuk skema lengkap
-- Jadual utama: users, categories, menu_items, orders, order_items, payments
-- Jadual tambahan: ingredients, grocery_lists, grocery_items, activity_logs
```

#### Konfigurasi & Utiliti
- `.config.php` - Konfigurasi pangkalan data, aplikasi, sesi, keselamatan
- `utils/Logger.php` - Pengurusan log ralat dan aktiviti
- `utils/Security.php` - CSRF, sanitasi input, hashing, kawalan akses

#### Models (6 fail)
- `UserModel.php` - Pendaftaran, log masuk, pengurusan pengguna
- `MenuModel.php` - CRUD menu dan kategori
- `OrderModel.php` - Pesanan (transaksi selamat), status, item
- `PaymentModel.php` - Rekod bayaran, kemaskini status
- `SalesModel.php` - Laporan jualan harian/bulanan/tahunan, trend, aliran tunai
- `GroceryModel.php` - Senarai belanja, auto-jana, toggle item

#### Controllers (7 fail)
- `AuthController.php` - Log masuk, daftar, log keluar, hala tuju berdasarkan peranan
- `MenuController.php` - Papar menu pelanggan, CRUD admin
- `OrderController.php` - Cart (session), checkout, jejak pesanan, API AJAX
- `PaymentController.php` - Halaman bayaran, proses bayaran tunai/QR
- `KitchenController.php` - Dashboard dapur, kemaskini status, staff buat order
- `SalesController.php` - Dashboard jualan, laporan, eksport CSV
- `GroceryController.php` - Dashboard belanja, cipta senarai, auto-jana, toggle AJAX

#### Frontend Views (18 fail)
- **Pelanggan:** `menu.php`, `cart.php`, `order-status.php`, `login.php`, `register.php`, `order-history.php`, `payment.php`
- **Dapur/Staff:** `kitchen/dashboard.php`, `kitchen/staff-order.php`
- **Admin:** `admin/sales-dashboard.php`, `admin/sales-daily.php`, `admin/sales-monthly.php`, `admin/sales-yearly.php`, `admin/popular-items.php`, `admin/menu-management.php`
- **Belanja:** `grocery/dashboard.php`, `grocery/checklist.php`, `grocery/history.php`, `grocery/create-list.php`
- **Shared:** `includes/header.php`, `includes/footer.php`

#### CSS & JavaScript
- `public/assets/css/style.css` - Sistem reka bentuk (dark theme, glassmorphism, animasi, responsif)
- `public/assets/js/app.js` - Cart AJAX, carian menu, loading, toast, modal
- `public/assets/js/kitchen.js` - Auto-refresh, kemaskini status, notifikasi browser

#### Routing
- `public/index.php` - Route dispatcher dengan 30+ halaman
- `public/.htaccess` - Apache rewrite rules dan header keselamatan
- API endpoints: `api-cart-add`, `api-cart-update`, `api-cart-remove`, `api-order-status`, `api-kitchen-update`

**Peranan Pengguna:**
1. Admin - Akses penuh (menu, jualan, dapur, belanja)
2. Staff - Ambil order, kemaskini dapur
3. Customer - Pesan makanan, jejak pesanan
4. Buyer (Tukang Pasar) - Urus senarai belanja
