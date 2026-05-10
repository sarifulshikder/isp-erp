
---

## ✅ Phase 26: Multi-MikroTik Support (100%) ✅
*Completed: May 10, 2026*

### Features:
- **Customer MikroTik Mode:**
  - `freeradius_only` — কোনো router এ add হয় না, RADIUS দিয়ে authenticate
  - `specific` — নির্দিষ্ট একটা router এ PPPoE/Hotspot user add
  - `all` — সব active router এ add, নতুন router যোগ হলে auto-push

- **MikroTik Device Actions:**
  - Test Connection — identity দেখায়
  - Online Users — active PPPoE sessions modal এ দেখায়
  - Export (Software → MikroTik) — Zone/Package/Status/Connection Type/Mode filter + live preview count
  - Import (MikroTik → Review Queue) — unknown users এনে review queue তে রাখে

- **Import Review Page (Network menu):**
  - Pending count badge sidebar এ দেখায়
  - Approve → নতুন customer create অথবা existing এ link
  - Reject → dismiss

- **Auto-push:**
  - নতুন MikroTik device active হলে সব `all` mode customer auto-push
  - CustomerObserver mode-aware — mode change হলে পুরনো device থেকে remove, নতুনে add

### New Files:
- `database/migrations/2026_05_10_000001_add_mikrotik_mode_to_customers_table.php`
- `database/migrations/2026_05_10_000002_create_mikrotik_import_reviews_table.php`
- `app/Models/MikrotikImportReview.php`
- `app/Observers/MikrotikDeviceObserver.php`
- `app/Filament/Resources/MikrotikImportReviews/MikrotikImportReviewResource.php`
- `app/Filament/Resources/MikrotikImportReviews/Pages/ListMikrotikImportReviews.php`

### Modified Files:
- `app/Models/Customer.php`
- `app/Models/MikrotikDevice.php`
- `app/Services/MikrotikService.php`
- `app/Observers/CustomerObserver.php`
- `app/Providers/AppServiceProvider.php`
- `app/Filament/Resources/Customers/Schemas/CustomerForm.php`
- `app/Filament/Resources/MikrotikDevices/Tables/MikrotikDevicesTable.php`
