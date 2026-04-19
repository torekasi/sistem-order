# Maklumat Log Masuk (Credentials) - Sistem Order

Senarai pengguna lalai (default) berserta peranan untuk log masuk ke dalam aplikasi Sistem Order.

> **Kata Laluan Umum:** Semua akaun di bawah menggunakan kata laluan **`password`**

| Peranan / Akses | Nama Pengguna / E-mel     | Kata Laluan | Keterangan Peranan                                                                           |
| :-------------- | :------------------------ | :---------- | :------------------------------------------------------------------------------------------- |
| **Super Admin** | superadmin@sistemorder.com | password    | Penguasa penuh sistem. Termasuk pentadbiran pengguna, konfigurasi tetapan utama, dan jualan. |
| **Admin**       | admin@sistemorder.com      | password    | Kawalan utama ke atas menu, jualan (laporan bulanan/harian), senarai beli barang, dan dapur. |
| **Staff**       | staff@sistemorder.com      | password    | Pekerja dapur atau operator "Walk-In". Hanya ada laluan ke monitor dapur dan muka Buat Order. |
| **Buyer**       | ros@gmail.com              | password    | Individu pembeli bahan stok dapur (Pergi Pasar).                                             |
| **Customer 1**  | siti@gmail.com             | password    | Akaun pembeli (pelanggan awam). Boleh lakukan pesanan, bayar, dan menjejak status.           |
| **Customer 2**  | ali@gmail.com              | password    | Sama seperti di atas.                                                                        |

## Nota Keselamatan Penting
- Di pelayan pengeluaran (production), sila pastikan anda **menukar kata laluan** bagi akaun Super Admin dan Admin serta-merta selepas instalasi! 
- Akaun pekerja dan pelanggan adalah sekadar rekod sampel yang dimasukkan semasa fasa pembangunan (rujuk skrip *database.sql*).
