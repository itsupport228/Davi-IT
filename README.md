# ITFlow — Migration dan seeder Laravel

Paket ini untuk proyek Laravel baru dengan MySQL di Laragon. Folder `database` mengikuti struktur proyek Laravel standar.

## Instalasi di Laragon

1. Buat database MySQL kosong, misalnya `itflow_dev`. Arahkan konfigurasi `DB_CONNECTION` dan `DB_DATABASE` di `.env` ke database itu dan isi pengaturan koneksi MySQL sesuai Laragon.
2. Salin isi folder `database` dari paket ini ke folder `database` proyek Laravel. Ganti file bawaan `0001_01_01_000000_create_users_table.php` dan `DatabaseSeeder.php` dengan versi ITFlow. Sisakan satu migration pembuat tabel `users` dan satu pembuat `sessions`. Migration bawaan untuk cache dan jobs dapat tetap dipakai.
3. Dari terminal Laragon di root proyek, jalankan

   ```bash
   php artisan config:clear
   php artisan migrate:fresh --seed
   php artisan migrate:status
   ```

   `migrate:fresh` menghapus semua tabel di database tujuan. Gunakan database pengembangan khusus ITFlow.

## Hasil seed

- 11 akun, terdiri dari 1 ATL, 2 TL, dan 8 IT Staff. NIP disimpan sebagai teks agar angka nol di depan tetap utuh. Password demo `it123` disimpan dalam bentuk hash.
- 5 relasi TL–staff. Raziq terhubung ke kedua TL.
- 3 board (`INF`, `DEV`, `OPS`), 8 sub-project, dan 8 task contoh yang mencakup ketiga status serta semua level prioritas. Task `INF-101` juga punya dua subtask dan satu komentar.
- Kode task unik. Saat mengimplementasikan pembuatan task, baca dan tingkatkan `projects.next_task_number` sambil mengunci baris project dengan `lockForUpdate()` dalam transaksi yang sama dengan insert task.

Migration dan seeder menyiapkan data untuk autentikasi NIP dan relasi akses. Tahap aplikasi masih perlu mengimplementasikan login, otorisasi server untuk task dan Excel, validasi pasangan kategori dan prioritas, serta aksi Kanban.
