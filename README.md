# Sistem Order

Sistem Order adalah sebuah aplikasi web yang dibangunkan menggunakan PHP untuk menguruskan pesanan makanan secara sistematik. Ia direka khas untuk melancarkan proses dari pelanggan membuat pesanan sehinggalah makanan disediakan di dapur dan pembayaran diselesaikan.

## Ciri-ciri Utama

* **Menu dan Pesanan:** Pelanggan boleh melihat senarai makanan dan minuman, menambah ke troli (cart), serta membuat pembayaran dan menjejaki status pesanan mereka.
* **Papan Pemuka Dapur (Kitchen Dashboard):** Kakitangan dapur akan menerima pesanan secara langsung dan boleh mengemas kini status penyediaan.
* **Pengurusan Pentadbir (Admin):** Memudahkan pengurusan pengguna, konfigurasi sistem, dan pengemaskinian menu.
* **Laporan Jualan:** Merekodkan jualan harian, bulanan, dan tahunan, serta menjejaki item menu yang paling popular.
* **Pengurusan Runcit (Grocery):** Mempunyai modul senarai semak runcit bagi memastikan bahan mentah sentiasa mencukupi.

## Teknologi

* PHP
* Pangkalan Data Relational (SQL)
* HTML, CSS, JavaScript (Frontend)
* Docker & Docker Compose (Penyediaan persekitaran pembangunan)

## Cara Pemasangan (Gunakan Docker)

Bagi menjalankan aplikasi ini di komputer anda, pastikan Docker dan Docker Compose telah dipasang.

1. Buka terminal (command prompt) di dalam folder projek.
2. Jalankan arahan berikut:
   ```bash
   docker-compose up -d
   ```
3. Akses aplikasi melalui pelayar web (browser) di pautan `http://localhost`.

## Pangkalan Data

Anda boleh merujuk kepada fail `database.sql` untuk melihat struktur pangkalan data (schema) yang digunakan di dalam sistem ini.
