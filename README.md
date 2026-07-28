<div align="center">

# 🌍 Supply Chain Risk Intelligence
### Enterprise Global Supply Chain Monitoring Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)

*Enterprise web platform untuk memantau risiko rantai pasok global melalui visualisasi data, integrasi Multi-API, analisis risiko, dan dashboard interaktif.*

</div>

---

# 📖 About Project

**Supply Chain Risk Intelligence** merupakan aplikasi berbasis web yang dibangun menggunakan **Laravel 12** untuk membantu perusahaan maupun organisasi dalam melakukan monitoring kondisi rantai pasok global secara real-time.

Sistem mengintegrasikan berbagai sumber data seperti informasi negara, pelabuhan dunia, cuaca, nilai tukar mata uang, serta berita internasional sehingga pengguna dapat melakukan analisis risiko dengan lebih cepat dan akurat.

Selain itu sistem menyediakan **Dashboard User** dan **Dashboard Administrator** yang memiliki hak akses berbeda menggunakan **Role Based Access Control (RBAC).**

---

# ✨ Main Features

## 👤 User Features

| Feature | Description |
|---------|-------------|
| Dashboard Overview | Menampilkan ringkasan kondisi Supply Chain Global |
| Global Supply Chain Threat Map | Visualisasi peta dunia menggunakan Leaflet |
| Risk Analytics | Analisis tingkat risiko tiap negara |
| Compare Countries | Membandingkan kondisi dua negara |
| Countries | Informasi lengkap negara |
| Port Intelligence | Informasi pelabuhan dunia |
| News Center | Berita internasional secara real-time |
| Exchange Rates | Nilai tukar mata uang |
| Weather Intelligence | Monitoring cuaca dunia |
| Watchlist | Menyimpan negara favorit |
| Reports | Laporan monitoring |

---

## 👨‍💼 Administrator Features

| Feature | Description |
|---------|-------------|
| Admin Dashboard | Dashboard monitoring administrator |
| User Management | CRUD User |
| Countries Management | CRUD Negara |
| Ports Management | CRUD Pelabuhan |
| News Management | CRUD Berita |
| Exchange Rates Management | Kelola nilai tukar mata uang |
| Weather Management | Kelola data cuaca |
| Risk Analytics | Monitoring tingkat risiko |
| Reports | Laporan Sistem |
| Settings | Pengaturan aplikasi |

---

# 📊 Dashboard Features

- Interactive Dashboard
- Global Port Map
- Country Comparison
- Interactive Charts (Chart.js)
- Leaflet Interactive Map
- Risk Distribution
- News Monitoring
- Weather Monitoring
- Exchange Rate Monitoring
- User Authentication
- Admin Panel
- Role Based Access Control

---

# 🛠️ Technology Stack

| Layer | Technology |
|---------|------------|
| Backend | Laravel 12 |
| Programming Language | PHP 8.3 |
| Database | MySQL |
| Frontend | Bootstrap 5 |
| CSS | Tailwind CSS & Custom CSS |
| Charts | Chart.js |
| Maps | Leaflet.js |
| API | REST API |
| Authentication | Laravel Authentication |
| Version Control | Git & GitHub |

---

# 🌐 APIs Used

- REST Countries API
- Open-Meteo API
- Exchange Rate API
- GNews API
- OpenStreetMap API

---

# 📸 Application Preview

## User Dashboard

- Dashboard Overview
- Risk Analytics
- Global Threat Map
- News Center
- Exchange Rates
- Weather Intelligence

## Admin Dashboard

- Dashboard Overview
- User Management
- Countries Management
- Ports Management
- News Management
- Risk Analytics
- Reports
- Settings

---

# ⚙️ Installation

## Clone Repository

```bash
git clone https://github.com/adrianazhar19/supply-chain-risk.git
```

Masuk ke folder project

```bash
cd supply-chain-risk
```

Install dependency

```bash
composer install
```

Copy file environment

```bash
cp .env.example .env
```

Generate Application Key

```bash
php artisan key:generate
```

Konfigurasi Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supply_chain_risk
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan Migrasi

```bash
php artisan migrate
```

Jalankan Seeder (Opsional)

```bash
php artisan db:seed
```

Jalankan Server

```bash
php artisan serve
```

Buka browser

```
http://127.0.0.1:8000
```

---

# 🔐 Authentication

Sistem menggunakan **Role Based Access Control (RBAC)**

### User

- Dashboard
- Monitoring
- Watchlist
- Reports

### Administrator

- Dashboard Admin
- CRUD User
- CRUD Countries
- CRUD Ports
- CRUD News
- Reports
- Settings

---

# 🗺️ Routes

| Route | Description |
|---------|------------|
| /login | Login User |
| /register | Register User |
| /dashboard | User Dashboard |
| /countries | Countries |
| /ports | Port Intelligence |
| /news | News Center |
| /exchange-rates | Exchange Rates |
| /weather | Weather Intelligence |
| /reports | Reports |
| /watchlist | Watchlist |
| /admin/dashboard | Admin Dashboard |
| /admin/users | User Management |
| /admin/countries | Countries Management |
| /admin/ports | Ports Management |
| /admin/news | News Management |

---

# 📂 Project Structure

```
supply-chain-risk/

├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/
├── vendor/
├── artisan
├── composer.json
└── package.json
```

---

# 🗄️ Database

Main Tables

- users
- countries
- ports
- risk_scores
- exchange_rates
- weather_data
- news_articles
- watchlists

---

# 🔒 Security

- Authentication
- Authorization
- Role Based Access Control (RBAC)
- CSRF Protection
- Password Hashing
- Middleware Protection

---

# 👨‍💻 Developer

**Adrian Azhar**

Universitas Harapan Medan

GitHub

https://github.com/adrianazhar19

---

# 📄 License

Project ini dibuat untuk keperluan **Tugas Akhir, Penelitian, dan Pembelajaran**.

---

<div align="center">

### ⭐ Supply Chain Risk Intelligence ⭐

**Enterprise Global Supply Chain Monitoring Platform**

Developed using ❤️ Laravel 12 • Bootstrap 5 • Chart.js • Leaflet.js

https://supply-chain-risk-production-fa94.up.railway.app

</div>