# Inventaris Laboratorium (Tipe B)

Praktikum Pemrograman Web (INF11197), Modul 1-7. Laravel + MySQL/MariaDB + PDO + Blade.

- Nama: Erlinda Amira Putri Sudarmono
- NIM: 2401020021
- Basis data: `inventaris_lab_2401020021`
- Route utama: `/inventaris`

## Cara Menjalankan

1. `composer install`
2. Salin `.env.example` menjadi `.env`, isi `DB_USERNAME` dan `DB_PASSWORD`
3. `php artisan key:generate`
4. Impor `database/inventaris_lab_2401020021.sql` (phpMyAdmin atau `mysql < berkas.sql`)
5. `php artisan serve`, buka `http://localhost:8000/inventaris`

## Penting: Daftarkan Helper

Pada `composer.json`, tambahkan di bagian `autoload`:

```json
"files": ["app/helpers.php"]
```

Lalu jalankan `composer dump-autoload`.

## Route

| Method | URL                    | Fungsi                         |
|--------|------------------------|--------------------------------|
| GET    | /inventaris            | Indeks, cari, filter           |
| POST   | /inventaris            | Create                         |
| GET    | /inventaris/{id}/edit  | Form edit                      |
| PUT    | /inventaris/{id}       | Update                         |
| DELETE | /inventaris/{id}       | Delete                         |

## Keamanan

- Semua query memakai PDO prepared statement
- Input dibersihkan dengan `trim` dan `strip_tags`, divalidasi server-side
- CSRF (`@csrf`) dan method spoofing (`@method`) untuk PUT dan DELETE
- Error dicatat dengan `report()`, pengguna hanya melihat pesan singkat
- `.env` tidak masuk repository
