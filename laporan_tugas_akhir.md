# LAPORAN TUGAS AKHIR

## SISTEM PENDUKUNG KEPUTUSAN KELOMPOK (GDSS) PEMILIHAN TIM RELAWAN TERBAIK BPBD MENGGUNAKAN METODE BORDA

---

## 1. Latar Belakang Permasalahan

Badan Penanggulangan Bencana Daerah (BPBD) memiliki peran vital dalam penanggulangan bencana di Indonesia. Salah satu elemen penting dalam operasional BPBD adalah Tim Relawan yang bertugas di lapangan. Evaluasi dan pemilihan Tim Relawan Terbaik merupakan kegiatan penting untuk mendorong peningkatan kinerja serta memberikan apresiasi terhadap tim yang memiliki performa unggul.

Permasalahan utama yang dihadapi dalam proses pemilihan Tim Relawan Terbaik adalah:
1. **Subjektivitas Penilaian**: Penilaian yang dilakukan secara manual rentan terhadap bias personal dari penilai.
2. **Multi-Asesor (Multirater)**: Keputusan perlu melibatkan beberapa asesor/penilai sehingga diperlukan mekanisme agregasi yang objektif.
3. **Multi-Kriteria**: Penilaian melibatkan banyak kriteria yang harus dipertimbangkan secara bersamaan.
4. **Konsensus Kelompok**: Kesulitan mencapai kesepakatan dari berbagai penilai yang mungkin memiliki preferensi berbeda.

Untuk mengatasi permasalahan tersebut, dikembangkan **Sistem Pendukung Keputusan Kelompok (Group Decision Support System/GDSS)** yang mengimplementasikan **Metode Borda** untuk mengagregasi preferensi/ranking dari setiap asesor menjadi satu keputusan kolektif yang mencerminkan kesepakatan kelompok.

---

## 2. Kriteria, Bobot Kriteria, dan Sifat Kriteria (Benefit/Cost)

### A. Kriteria Penilaian

Kriteria penilaian dalam sistem ini bersifat **dinamis** dan dapat dikonfigurasi oleh Administrator melalui menu **Kelola Kriteria**. Setiap kriteria memiliki:
- **Kode Kriteria**: Identifikasi unik (contoh: C1, C2, C3, dst.)
- **Nama Kriteria**: Deskripsi kriteria yang dinilai
- **Bobot Kriteria**: Persentase kepentingan relatif (total keseluruhan bobot = 100%)
- **Jenis Kriteria**: Benefit atau Cost
- **Subkriteria**: Opsi nilai yang tersedia untuk setiap kriteria

Contoh Kriteria yang dapat digunakan:

| Kode | Nama Kriteria | Bobot (%) | Jenis |
|------|---------------|-----------|-------|
| C1 | Kecepatan Respon | 25 | Benefit |
| C2 | Kerjasama Tim | 20 | Benefit |
| C3 | Keterampilan Teknis | 20 | Benefit |
| C4 | Kedisiplinan | 20 | Benefit |
| C5 | Komunikasi | 15 | Benefit |
| **Total** | | **100** | |

### B. Sifat Kriteria (Benefit/Cost)

- **Benefit**: Semakin tinggi nilai maka semakin baik kinerja tim relawan
- **Cost**: Semakin rendah nilai maka semakin baik (misalnya: waktu keterlambatan, jumlah insiden, dll.)

### C. Subkriteria (Skala Penilaian)

Setiap kriteria memiliki **Subkriteria** yang berfungsi sebagai skala penilaian. Contoh:

| Subkriteria | Nilai |
|-------------|-------|
| Sangat Baik | 5 |
| Baik | 4 |
| Cukup | 3 |
| Kurang | 2 |
| Sangat Kurang | 1 |

---

## 3. Alternatif (Tim Relawan yang Dinilai)

Alternatif dalam proyek ini adalah **Tim Relawan** yang kinerjanya akan dievaluasi. Data alternatif bersifat dinamis dan dapat dikelola melalui menu **Kelola Tim Relawan**. Setiap alternatif memiliki atribut:

- **Kode Alternatif**: Identifikasi unik (contoh: A01, A02, dst.)
- **Nama Tim**: Nama tim relawan
- **Ketua Tim**: Nama ketua tim
- **Wilayah**: Area operasional tim
- **Jumlah Anggota**: Total anggota tim

Contoh Data Alternatif:

| Kode | Nama Tim | Ketua Tim | Wilayah | Jumlah Anggota |
|------|----------|-----------|---------|----------------|
| A01 | Tim Rescue Alpha | Ahmad Yani | Medan Kota | 12 |
| A02 | Tim SAR Omega | Budi Santoso | Deli Serdang | 10 |
| A03 | Tim Medis Beta | Siti Nurbaya | Binjai | 8 |
| A04 | Tim Evakuasi Gamma | Rizki Firmansyah | Langkat | 15 |
| A05 | Tim Logistik Delta | Dewi Kusuma | Medan Timur | 10 |

---

## 4. Langkah Perhitungan GDSS dengan Metode Borda

Metode yang digunakan adalah **GDSS Borda** untuk agregasi peringkat dari beberapa asesor menjadi satu keputusan kolektif.

### A. Tahap 1: Input Penilaian oleh Asesor

Setiap asesor memberikan penilaian untuk setiap alternatif pada setiap kriteria:

1. Asesor login ke sistem dan mengakses menu **Input Penilaian**
2. Untuk setiap Tim Relawan dan setiap Kriteria, asesor memilih nilai subkriteria yang sesuai
3. Sistem menyimpan data penilaian: (id_alternatif, id_kriteria, id_subkriteria, id_user)

### B. Tahap 2: Perhitungan Nilai Total Per Asesor

Untuk setiap asesor, sistem menghitung nilai total setiap alternatif:

**Rumus Nilai Total Alternatif i oleh Asesor j:**

```
Total_ij = Σ (Nilai_Subkriteria_ik × Bobot_k / 100)
```

Dimana:
- `Nilai_Subkriteria_ik` = Nilai subkriteria yang dipilih untuk alternatif i pada kriteria k
- `Bobot_k` = Bobot kriteria k (dalam persentase)

### C. Tahap 3: Penetapan Ranking Per Asesor

Berdasarkan nilai total, sistem mengurutkan alternatif dari nilai tertinggi ke terendah untuk setiap asesor, menghasilkan ranking per asesor:

| Alternatif | Nilai Total (Asesor 1) | Ranking (Asesor 1) |
|------------|------------------------|--------------------|
| Tim A | 4.20 | 1 |
| Tim B | 3.95 | 2 |
| Tim C | 3.80 | 3 |
| ... | ... | ... |

### D. Tahap 4: Penetapan Poin Borda

Sistem menerapkan **Poin Borda** berdasarkan ranking:

**Rumus Poin Borda Standar:**
```
Poin_Borda = n - ranking + 1
```
Dimana `n` = jumlah alternatif

Contoh untuk 5 alternatif:
| Ranking | Poin Borda |
|---------|------------|
| 1 | 5 |
| 2 | 4 |
| 3 | 3 |
| 4 | 2 |
| 5 | 1 |

**Catatan**: Sistem mendukung **konfigurasi poin Borda kustom** yang dapat diubah oleh Administrator.

### E. Tahap 5: Agregasi Skor Borda Kolektif

Setiap alternatif menerima poin Borda dari setiap asesor, kemudian dijumlahkan:

**Rumus Total Skor Borda Alternatif i:**
```
Total_Skor_Borda_i = Σ Poin_Borda_ij (untuk semua asesor j)
```

### F. Tahap 6: Penentuan Ranking Akhir

Alternatif diurutkan berdasarkan Total Skor Borda tertinggi:

| Alternatif | Poin Asesor 1 | Poin Asesor 2 | Poin Asesor 3 | Total Skor Borda | Ranking Akhir |
|------------|---------------|---------------|---------------|------------------|---------------|
| Tim C | 4 | 5 | 5 | 14 | 1 |
| Tim A | 5 | 4 | 4 | 13 | 2 |
| Tim B | 3 | 3 | 3 | 9 | 3 |
| Tim D | 2 | 2 | 1 | 5 | 4 |
| Tim E | 1 | 1 | 2 | 4 | 5 |

**Kesimpulan Keputusan**: Tim dengan total skor Borda tertinggi ditetapkan sebagai **Tim Relawan Terbaik**.

---

## 5. Langkah Perhitungan Metode TOPSIS

Sistem ini juga mengimplementasikan **Metode TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution)** sebagai metode alternatif untuk pengambilan keputusan multi-kriteria.

### A. Tahap 1: Matriks Keputusan (X)

Matriks keputusan dibentuk dari nilai rata-rata penilaian semua asesor untuk setiap alternatif pada setiap kriteria:

| Alternatif | K1 | K2 | K3 | K4 | K5 |
|------------|-----|-----|-----|-----|-----|
| A1 | x₁₁ | x₁₂ | x₁₃ | x₁₄ | x₁₅ |
| A2 | x₂₁ | x₂₂ | x₂₃ | x₂₄ | x₂₅ |
| A3 | x₃₁ | x₃₂ | x₃₃ | x₃₄ | x₃₅ |
| ... | ... | ... | ... | ... | ... |

### B. Tahap 2: Normalisasi Matriks (R)

Setiap elemen matriks dinormalisasi menggunakan rumus:

```
r_ij = x_ij / √(Σ x_ij²)
```

Dimana:
- `r_ij` = Nilai ternormalisasi alternatif i pada kriteria j
- `x_ij` = Nilai alternatif i pada kriteria j
- Pembagi adalah akar kuadrat dari jumlah kuadrat semua nilai pada kolom j

**Contoh Perhitungan Pembagi:**
```
Pembagi_j = √(x₁j² + x₂j² + x₃j² + ... + xₙj²)
```

### C. Tahap 3: Matriks Ternormalisasi Terbobot (Y)

Setiap elemen matriks ternormalisasi dikalikan dengan bobot kriteria:

```
y_ij = w_j × r_ij
```

Dimana:
- `y_ij` = Nilai ternormalisasi terbobot
- `w_j` = Bobot kriteria j (dalam persentase)
- `r_ij` = Nilai ternormalisasi

### D. Tahap 4: Solusi Ideal Positif (A⁺) dan Negatif (A⁻)

**Solusi Ideal Positif (A⁺):**
- Untuk kriteria **Benefit**: nilai maksimum dari kolom
- Untuk kriteria **Cost**: nilai minimum dari kolom

```
A⁺ = {y₁⁺, y₂⁺, y₃⁺, ..., yₙ⁺}
```

**Solusi Ideal Negatif (A⁻):**
- Untuk kriteria **Benefit**: nilai minimum dari kolom
- Untuk kriteria **Cost**: nilai maksimum dari kolom

```
A⁻ = {y₁⁻, y₂⁻, y₃⁻, ..., yₙ⁻}
```

**Contoh:**

| Kriteria | Jenis | A⁺ (max/min) | A⁻ (min/max) |
|----------|-------|--------------|--------------|
| K1 | Benefit | max(y_i1) | min(y_i1) |
| K2 | Benefit | max(y_i2) | min(y_i2) |
| K3 | Cost | min(y_i3) | max(y_i3) |

### E. Tahap 5: Jarak ke Solusi Ideal (D⁺ dan D⁻)

**Jarak ke Solusi Ideal Positif:**
```
D_i⁺ = √[Σ (y_ij - y_j⁺)²]
```

**Jarak ke Solusi Ideal Negatif:**
```
D_i⁻ = √[Σ (y_ij - y_j⁻)²]
```

### F. Tahap 6: Nilai Preferensi (V)

Nilai preferensi setiap alternatif dihitung dengan rumus:

```
V_i = D_i⁻ / (D_i⁺ + D_i⁻)
```

Dimana:
- `V_i` = Nilai preferensi alternatif i (0 ≤ V ≤ 1)
- Alternatif dengan nilai V tertinggi adalah yang terbaik

### G. Contoh Hasil Perhitungan TOPSIS

| Alternatif | D⁺ | D⁻ | V (Preferensi) | Ranking |
|------------|------|------|----------------|---------|
| Tim A | 0.0234 | 0.0412 | 0.638 | 2 |
| Tim B | 0.0189 | 0.0489 | 0.721 | 1 |
| Tim C | 0.0356 | 0.0298 | 0.456 | 3 |
| Tim D | 0.0412 | 0.0178 | 0.302 | 4 |
| Tim E | 0.0489 | 0.0134 | 0.215 | 5 |

**Kesimpulan**: Tim B memiliki nilai preferensi tertinggi (0.721), sehingga ditetapkan sebagai alternatif terbaik berdasarkan metode TOPSIS.

---

## 6. Aplikasi yang Dibuat

### A. Tujuan Aplikasi

Aplikasi **GDSS Pemilihan Tim Relawan Terbaik BPBD** dirancang untuk:
- Mendigitalisasi proses penilaian Tim Relawan
- Menghilangkan bias subjektif dengan memformalisasi proses penilaian
- Memfasilitasi kolaborasi antar asesor (multirater) dalam lingkungan GDSS
- Menyajikan hasil keputusan akhir yang teragregasi, transparan, dan mudah dipertanggungjawabkan
- Menyediakan visualisasi hasil dalam bentuk grafik dan chart

### B. Modul dan Fungsi Utama

#### 1. Modul Autentikasi (`modules/auth/`)
- **Login/Logout**: Autentikasi pengguna dengan validasi session
- **Manajemen Profil**: Pengguna dapat mengelola profil akun

#### 2. Modul Master Data (`modules/master/`)

**a. Kelola Kriteria (`kriteria.php`)**
- Menambah, mengubah, dan menghapus kriteria penilaian
- Mengatur bobot kriteria (validasi total = 100%)
- Mengatur jenis kriteria (Benefit/Cost)
- Mengelola subkriteria (skala penilaian)

**b. Kelola Alternatif (`alternatif.php`)**
- Menambah, mengubah, dan menghapus data Tim Relawan
- Menyimpan informasi detail tim: nama, ketua, wilayah, jumlah anggota

**c. Kelola User (`user.php`)**
- Manajemen akun pengguna (Admin, BPBD, Asesor)
- Pengaturan hak akses berbasis role

#### 3. Modul Penilaian (`modules/penilaian/`)

**a. Input Penilaian Batch (`input.php`)**
- Formulir digital untuk asesor melakukan penilaian
- Tampilan semua Tim Relawan dan Kriteria dalam satu halaman
- Indikator status penilaian (Belum Dinilai, Sebagian, Lengkap)
- Menyimpan catatan opsional untuk setiap penilaian

**b. Data Penilaian (`data.php`)**
- Melihat rekap data penilaian yang telah dimasukkan
- Filter berdasarkan asesor atau alternatif

#### 4. Modul Borda/Perhitungan (`modules/borda/`)

**a. Proses Voting Borda (`voting.php`)**
- Konfigurasi poin Borda per ranking (dapat dikustomisasi)
- Tombol proses perhitungan Metode Borda
- Statistik: jumlah alternatif, asesor aktif, total penilaian

**b. Hasil Perhitungan (`hasil.php`)**
- Tampilan **Podium Top 3** Tim Relawan Terbaik
- **Visualisasi Chart.js**:
  - Bar Chart: Perbandingan skor antar tim
  - Doughnut Chart: Distribusi skor
  - Horizontal Bar Chart: Ranking visual
- Tabel ranking lengkap dengan detail skor
- Keterangan rumus dan metode yang digunakan

**c. Laporan (`reports/laporan.php`)**
- Cetak laporan hasil perhitungan dalam format printable

### C. Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| **Bahasa Pemrograman** | PHP 7+ |
| **Database** | MySQL / MariaDB |
| **Framework CSS** | Bootstrap 5 |
| **Icon Library** | Bootstrap Icons |
| **Charting Library** | Chart.js |
| **Server** | Apache (XAMPP) |

### D. Struktur Database

```
database: gdss_relawan_bpbd
├── user                 # Tabel pengguna sistem
├── kriteria             # Tabel kriteria penilaian
├── subkriteria          # Tabel subkriteria/skala nilai
├── alternatif           # Tabel tim relawan
├── penilaian            # Tabel data penilaian asesor
├── hasil_borda          # Tabel hasil agregasi Borda
├── detail_borda         # Tabel detail poin per asesor
└── konfigurasi_borda    # Tabel konfigurasi poin Borda
```

### E. Role Pengguna

| Role | Hak Akses |
|------|-----------|
| **Admin** | Full access: kelola semua data master, user, lihat hasil |
| **BPBD** | Proses perhitungan Borda, lihat hasil, cetak laporan |
| **Asesor** | Input penilaian, lihat penilaian sendiri |

---

## 7. Tampilan Antarmuka Aplikasi

### Gambar 7.1 - Dashboard Sistem
Menampilkan statistik ringkas: Total Tim Relawan, Kriteria Penilaian, Tim Asesor, dan Total Penilaian. Juga menampilkan hasil ranking terbaru.

### Gambar 7.2 - Kelola Kriteria
Halaman administrator untuk mengkonfigurasi kriteria penilaian beserta bobot dan subkriteria.

### Gambar 7.3 - Kelola Tim Relawan (Alternatif)
Halaman untuk mengelola data tim relawan yang akan dinilai.

### Gambar 7.4 - Input Penilaian Asesor
Antarmuka penilaian batch yang memungkinkan asesor menilai semua tim relawan dan kriteria dalam satu halaman dengan indikator status kelengkapan.

### Gambar 7.5 - Proses Perhitungan Borda
Halaman untuk mengkonfigurasi poin Borda dan memulai proses perhitungan agregasi.

### Gambar 7.6 - Hasil Perhitungan dengan Visualisasi
Menampilkan podium Top 3, grafik bar chart, doughnut chart, dan tabel ranking lengkap.

---

## 8. Kesimpulan

Sistem Pendukung Keputusan Kelompok (GDSS) untuk Pemilihan Tim Relawan Terbaik BPBD telah berhasil dikembangkan dengan mengimplementasikan dua metode pengambilan keputusan:

1. **Metode Borda** - untuk agregasi preferensi/ranking dari beberapa asesor menjadi keputusan kolektif
2. **Metode TOPSIS** - untuk pengambilan keputusan multi-kriteria berdasarkan kedekatan dengan solusi ideal

Sistem ini berhasil:

1. **Mendigitalisasi** proses penilaian yang sebelumnya manual
2. **Mengurangi subjektivitas** dengan kriteria dan bobot yang terukur
3. **Mengakomodasi multi-asesor** dengan mekanisme agregasi Borda
4. **Menyediakan alternatif metode** dengan TOPSIS untuk validasi keputusan
5. **Menyajikan hasil** dalam bentuk ranking dan visualisasi yang mudah dipahami
6. **Memberikan transparansi** dengan detail perhitungan yang dapat diaudit

Kombinasi **Metode Borda** dan **Metode TOPSIS** memberikan fleksibilitas dalam pengambilan keputusan dan memungkinkan validasi silang (cross-validation) antara kedua metode untuk hasil yang lebih akurat dan dapat dipertanggungjawabkan.

---

**Dokumen ini dibuat sebagai bagian dari Laporan Tugas Akhir Sistem Informasi**
