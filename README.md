# ISP ERP — Internet Service Provider Management System

বাংলাদেশের ছোট-মাঝারি ISP কোম্পানির জন্য সম্পূর্ণ ব্যবস্থাপনা সফটওয়্যার।

## Features
- Customer Management, MikroTik Multi-Router, FreeRADIUS
- Invoice, Payment, bKash, Nagad
- OLT/ONU Monitoring, SMS Notification
- Support Tickets, Network Map, Inventory
- LibreNMS, RBAC, Reports

## Tech Stack
- Laravel 11 + PHP 8.4 + Filament 5
- MySQL 8.0, Redis 7, FreeRADIUS 3.2.3
- Docker + Ubuntu 26.04 LTS

## Services
- isp_laravel — Admin Panel (port 80)
- isp_mysql — MySQL Database
- isp_redis — Redis Cache
- isp_radius — FreeRADIUS (1812/1813)
- isp_librenms — Network Monitor (8000)

## Resource Usage
- Total RAM: ~512MB only

## Developer
Sariful Shikder — sarifulshikder@gmail.com
