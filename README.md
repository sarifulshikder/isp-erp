# 🌐 ISP ERP — Internet Service Provider Management System

> একটি সম্পূর্ণ বিনামূল্যের ISP ERP সিস্টেম যা Laravel 11 + Filament 5 + Docker দিয়ে তৈরি।

---

## 📋 Project Overview

**লক্ষ্য:** একটি ISP (Internet Service Provider) এর জন্য Production-Ready ERP সিস্টেম যেখানে Customer Management, Billing, MikroTik Integration, Network Monitoring সহ সব কিছু থাকবে।

**Server:** Ubuntu 26.04 LTS | Docker Engine 29.4.2 | Docker Compose v5.1.3

**Project Path on Server:** `~/isp-erp/`

---

## 🛠️ Tech Stack

| Component | Technology | Version | Status |
|---|---|---|---|
| Backend Framework | Laravel | 11.51.0 | ✅ Done |
| Admin Panel | Filament | 5.6.2 | ✅ Done |
| Database | MySQL | 8.0 | ✅ Done |
| Cache/Queue | Redis | 7 Alpine | ✅ Done |
| Authentication | Laravel Sanctum | Latest | ✅ Done |
| Network Auth | FreeRADIUS | 3.2.3 | ✅ Running (not configured) |
| Network Monitor | LibreNMS | Latest | ✅ Running (not configured) |
| Inventory | Snipe-IT | Latest | ✅ Running (not configured) |
| Automation | n8n | 2.18.7 | ✅ Running (not configured) |
| Ticketing | Zammad | Latest | ❌ Not configured yet |
| Web Server | Nginx + PHP 8.4 | Alpine | ✅ Done |

---

## 🐳 Docker Services

**Location:** `~/isp-erp/docker-compose.yml`

| Container | Port | Status |
|---|---|---|
| isp_laravel | :80 | ✅ Running |
| isp_mysql | internal | ✅ Running |
| isp_redis | internal | ✅ Running |
| isp_radius | :1812/:1813 UDP | ✅ Running |
| isp_librenms | :8000 | ✅ Running |
| isp_snipeit | :8081 | ✅ Running |
| isp_n8n | :5678 | ✅ Running |
| isp_zammad | - | ❌ Not added yet |

**Start all services:**
```bash
cd ~/isp-erp
sudo docker compose up -d
```

---

## 🗄️ Database Structure

**Database:** `isp_db` on MySQL container

| Table | Description | Status |
|---|---|---|
| users | Admin users | ✅ Done |
| packages | Internet packages | ✅ Done |
| customers | ISP customers | ✅ Done |
| invoices | Billing invoices | ✅ Done |
| payments | Payment records | ✅ Done |
| mikrotik_devices | MikroTik routers | ✅ Done |
| personal_access_tokens | API tokens | ✅ Done |

---

## 📁 Project Structure

```
~/isp-erp/
├── docker-compose.yml          # All Docker services
├── .env                        # Environment variables
├── radius/                     # FreeRADIUS config (empty)
└── backend/                    # Laravel 11 application
    ├── app/
    │   ├── Models/
    │   │   ├── Customer.php
    │   │   ├── Package.php
    │   │   ├── Invoice.php
    │   │   ├── Payment.php
    │   │   └── MikrotikDevice.php
    │   ├── Http/Controllers/   # API Controllers
    │   │   ├── DashboardController.php
    │   │   ├── CustomerController.php
    │   │   ├── PackageController.php
    │   │   ├── InvoiceController.php
    │   │   ├── PaymentController.php
    │   │   └── MikrotikController.php
    │   └── Filament/
    │       ├── Widgets/
    │       │   └── StatsOverview.php  # Dashboard statistics
    │       └── Resources/
    │           ├── Customers/         # ✅ Done + Package dropdown fixed
    │           ├── Packages/          # ✅ Done
    │           ├── Invoices/          # ⚠️ Needs relationship fixes
    │           ├── Payments/          # ⚠️ Needs relationship fixes
    │           └── MikrotikDevices/   # ⚠️ Needs connection test feature
    ├── routes/
    │   ├── api.php             # 26 API routes
    │   └── web.php
    └── database/migrations/    # All migrations done
```

---

## ✅ Completed Features

### Phase 1 — Server Setup ✅
- Ubuntu 26.04 LTS server configured
- Docker + Docker Compose installed
- UFW Firewall configured (ports: 22, 80, 443, 8000, 8081, 5678, 1812, 1813)
- All Docker services running

### Phase 2 — Docker Infrastructure ✅
- MySQL 8.0 with `isp_db` and `librenms` databases
- Redis 7 with password authentication
- FreeRADIUS 3.2.3 running
- LibreNMS running (needs device configuration)
- Snipe-IT running (needs initial setup)
- n8n running (needs workflow configuration)

### Phase 3 — Laravel ERP Core ✅
- Laravel 11 installed with PHP 8.4
- Filament 5 Admin Panel configured
- All Models with relationships
- All API Controllers (26 routes)
- Database migrations complete
- Admin user created
- Dashboard with Stats Widget
- Customer Management (with Package dropdown)
- Package Management

---

## ⚠️ Partially Done / Needs Work

### Filament Resources
- **Invoices Resource** — `customer_id` and `package_id` fields need Select dropdowns (same fix as Customer)
- **Payments Resource** — `invoice_id` and `customer_id` need Select dropdowns
- **MikroTik Devices Resource** — needs "Test Connection" button

### Fix Pattern (for Invoice/Payment dropdowns):
Edit file: `backend/app/Filament/Resources/Invoices/Schemas/InvoiceForm.php`
Replace `TextInput::make('customer_id')->numeric()` with:
```php
Select::make('customer_id')
    ->label('Customer')
    ->options(Customer::pluck('name', 'id'))
    ->required()
    ->searchable(),
```

---

## ❌ Not Started Yet

### Phase 4 — FreeRADIUS + MikroTik Integration
- [ ] Configure FreeRADIUS with MySQL backend (`~/isp-erp/radius/` folder is empty)
- [ ] Connect MikroTik router to RADIUS server (IP: server's public IP, ports 1812/1813)
- [ ] Auto enable/disable customer on payment
- [ ] PPPoE user management via MikroTik API
- [ ] MikroTik PHP library: `composer require bencoder/routeros-api`

**FreeRADIUS MySQL config needed in:** `~/isp-erp/radius/`

### Phase 5 — Payment Gateway
- [ ] bKash Payment API integration
- [ ] Nagad Payment API integration
- [ ] Auto invoice generation on payment
- [ ] SMS notification on payment (via SMS gateway)

### Phase 6 — Zammad Ticketing
- [ ] Zammad needs Elasticsearch — requires separate docker-compose setup
- [ ] Reference: `docker-compose -f zammad-docker-compose.yml up`
- [ ] Official repo: `https://github.com/zammad/zammad-docker-compose`

### Phase 7 — LibreNMS Configuration
- [ ] Add devices via `http://SERVER_IP:8000`
- [ ] Configure SNMP on network devices
- [ ] Set up alerting rules

### Phase 8 — n8n Automation
- [ ] Access: `http://SERVER_IP:5678`
- [ ] Create workflow: Customer expires → Auto suspend → Send SMS
- [ ] Create workflow: POP down → Auto ticket in Zammad → SMS alert

### Phase 9 — SSL + Production
- [ ] Install Certbot: `sudo apt install certbot`
- [ ] Get SSL: `sudo certbot --nginx -d yourdomain.com`
- [ ] Setup cron for auto database backup
- [ ] Configure domain name

---

## 🔑 Credentials & Access

| Service | URL | Credentials |
|---|---|---|
| Admin Panel | `http://SERVER_IP/admin` | sarifulshikder@gmail.com |
| LibreNMS | `http://SERVER_IP:8000` | Not configured |
| Snipe-IT | `http://SERVER_IP:8081` | Not configured |
| n8n | `http://SERVER_IP:5678` | admin / Admin@2026 |
| MySQL | internal:3306 | isp_user / ISP@Secure#2026 |

**Note:** Replace `SERVER_IP` with actual server IP (currently `10.5.31.34` private, public IP separate)

---

## 🚀 Next Steps for AI Agent

If you are an AI agent continuing this project, follow this order:

1. **Fix Invoice & Payment dropdowns** (same pattern as CustomerForm.php fix)
2. **Setup Zammad** using official docker-compose from GitHub
3. **Configure FreeRADIUS** MySQL backend in `~/isp-erp/radius/`
4. **MikroTik Integration** using RouterOS API library
5. **bKash/Nagad** payment gateway integration
6. **n8n workflows** for automation
7. **SSL setup** with domain

### Useful Commands
```bash
# Start all services
cd ~/isp-erp && sudo docker compose up -d

# Check status
sudo docker compose ps

# Laravel artisan
sudo docker exec -w /app isp_laravel php artisan [command]

# View Laravel logs
sudo docker exec -w /app isp_laravel tail -f storage/logs/laravel.log

# MySQL access
sudo docker exec -it isp_mysql mysql -u isp_user -p'ISP@Secure#2026' isp_db

# Fix permissions if needed
sudo docker exec -w /app isp_laravel chmod -R 777 storage bootstrap/cache
```

---

## 📝 Known Issues & Solutions

| Issue | Solution |
|---|---|
| `/app` empty after container restart | `sudo docker compose restart laravel` then wait 10s |
| Permission denied on storage | `sudo docker exec -w /app isp_laravel chmod -R 777 storage bootstrap/cache` |
| MySQL connection timeout | Check if all containers on same network: `sudo docker inspect isp_laravel` |
| Filament resource syntax error | Use `BackedEnum\|string\|null` for `$navigationIcon`, use `Schema` not `Form` |

---

## 👤 Developer

- **Name:** Sariful Shikder
- **Email:** sarifulshikder@gmail.com
- **GitHub:** https://github.com/sarifulshikder/isp-erp

---

*Last updated: May 2026 | Built with ❤️ using Laravel 11 + Filament 5*
