# 🕌 Tanah Suci Travel Web

**Tanah Suci Travel** adalah sebuah aplikasi web dinamis untuk agensi perjalanan Umrah dan Haji. Website ini dibangun menggunakan PHP Native (tanpa framework) dengan struktur kode yang bersih, dilengkapi dengan **Custom CMS (Admin Panel)** untuk pengelolaan konten secara menyeluruh dan fitur **Group Chat Komunitas**.

![Status Project](https://img.shields.io/badge/Status-Completed-success)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%207.4-blue)
![Database](https://img.shields.io/badge/Database-MySQL-orange)

## 🌟 Fitur Utama

### 🏠 Sisi Pengunjung (Public)
* **Halaman Landing Dinamis:** Menampilkan Hero section, fitur keunggulan, dan paket populer yang bisa diubah dari admin.
* **Katalog Paket:** Daftar paket Umrah & Haji lengkap dengan harga, fasilitas, dan detail lainnya.
* **Halaman Artikel Dinamis:** Mendukung pembuatan halaman artikel/blog SEO-friendly (cth: `article.php?slug=judul`).
* **Galeri & Testimoni:** Menampilkan dokumentasi perjalanan dan ulasan jamaah.
* **Fitur Chat Komunitas:** Group chat real-time sederhana untuk diskusi antar pengunjung/jamaah.
* **Formulir Inquiry:** Pengunjung dapat mengirimkan pertanyaan atau pemesanan paket yang langsung masuk ke dashboard admin.
* **Responsive Design:** Tampilan optimal di Desktop, Tablet, dan Mobile.

### 🔐 Sisi Admin (Control Panel)
* **Dashboard Statistik:** Ringkasan jumlah paket, pesan masuk, dan aktivitas admin.
* **Manajemen Konten (CMS):**
    * **Pengaturan Umum:** Ubah nama situs, logo, warna tema, dan informasi kontak footer.
    * **Kelola Paket:** Tambah/Edit/Hapus paket perjalanan, harga, dan fitur-fiturnya.
    * **Kelola Artikel:** Buat artikel promo dengan fitur editor konten (JSON based features & cards).
    * **Kelola Galeri & Testimoni:** Upload foto dan kelola ulasan pelanggan.
* **Manajemen Pesan:** Melihat dan membalas pesan masuk dari pengunjung (integrasi WhatsApp).
* **Manajemen Admin:** Menambah akun admin baru dan melihat log aktivitas.

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP (Native/Vanilla)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3 (Custom CSS Variable & Flexbox/Grid), JavaScript (Vanilla)
* **Icons:** FontAwesome 6
* **Font:** Google Fonts (Cinzel & Outfit)
* **Server:** Apache (XAMPP/WAMP/Laragon)

## 📂 Struktur Folder

```text
/
├── admin/                  # Halaman & Logika Dashboard Admin
│   ├── includes/           # Header, Footer, Auth helper admin
│   ├── articles.php        # CRUD Artikel
│   ├── packages.php        # CRUD Paket
│   ├── settings.php        # Pengaturan Website
│   └── ... (file admin lainnya)
├── config/
│   └── database.php        # Koneksi Database (PDO) & Helper Functions
├── uploads/                # Folder penyimpanan gambar (jika ada)
├── article.php             # Halaman detail artikel (Frontend)
├── chat.php                # Halaman Chat Komunitas
├── chat_api.php            # API Backend untuk Chat
├── index.php               # Halaman Utama (Landing Page)
├── komunitas.php           # Interface Chat Komunitas
├── database.sql            # Skema Database Utama
├── chat.sql                # Skema Database Chat
└── README.md               # Dokumentasi Proyek
