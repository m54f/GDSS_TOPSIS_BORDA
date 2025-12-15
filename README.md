# GDSS Relawan BPBD

Sistem Pendukung Keputusan Kelompok (GDSS) untuk Pemilihan Tim Relawan Terbaik BPBD menggunakan Metode TOPSIS dan Borda.

## 🎯 Fitur Utama

- **Multi-Asesor**: Mendukung penilaian dari beberapa asesor
- **Metode TOPSIS**: Pengambilan keputusan multi-kriteria
- **Metode Borda**: Agregasi ranking kolektif
- **Visualisasi**: Grafik dan chart interaktif dengan Chart.js
- **Role-Based Access**: Admin, BPBD, dan Asesor

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 7+ |
| Database | MySQL/MariaDB |
| Frontend | Bootstrap 5, Chart.js |
| Server | Apache (XAMPP) |

## 📦 Instalasi

1. **Clone/Copy** project ke folder `htdocs`:
   ```
   C:\xampp\htdocs\gdss_relawan_bpbd\
   ```

2. **Import Database**:
   - Buat database `gdss_relawan_bpbd`
   - Import file SQL (jika tersedia)

3. **Konfigurasi** (opsional):
   - Edit `config/database.php` jika diperlukan

4. **Akses Aplikasi**:
   ```
   http://localhost/gdss_relawan_bpbd/gdss_relawan_bpbd/
   ```

## 📁 Struktur Folder

```
gdss_relawan_bpbd/
├── config/          # Konfigurasi database
├── includes/        # Header & footer
├── modules/
│   ├── auth/        # Login & profil
│   ├── master/      # Kriteria, alternatif, user
│   ├── penilaian/   # Input penilaian
│   └── borda/       # Perhitungan & hasil
├── reports/         # Laporan cetak
└── assets/          # CSS, JS, images
```

## 👥 Role Pengguna

| Role | Akses |
|------|-------|
| **Admin** | Kelola semua data master & user |
| **BPBD** | Proses perhitungan, lihat hasil |
| **Asesor** | Input penilaian |

## 📄 Dokumentasi

Lihat [Laporan Tugas Akhir](laporan_tugas_akhir.md) untuk penjelasan lengkap metode dan sistem.

## 📝 Lisensi

Dibuat untuk keperluan Tugas Akhir Sistem Informasi.
