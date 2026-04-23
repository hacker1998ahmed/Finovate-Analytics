# Finovate – AHMED EG
## Multi-Tenant E-Invoice System for Egypt (ETA Compliant)

نظام ERP متكامل لإدارة الفاتورة الإلكترونية في مصر متوافق 100% مع متطلبات هيئة الضرائب المصرية (ETA)

---

## 📋 المحتويات

- [المميزات](#المميزات)
- [المتطلبات](#المتطلبات)
- [التثبيت](#التثبيت)
- [الهيكلية](#الهيكلية)
- [API Endpoints](#api-endpoints)
- [استخدام النظام](#استخدام-النظام)
- [الأمان](#الأمان)

---

## ✨ المميزات

### 🔐 Multi-Tenant Architecture
- دعم غير محدود للشركات
- بيانات معزولة تمامًا لكل شركة
- إعدادات مستقلة لكل شركة

### 🇪🇬 التكامل مع ETA
- OAuth2 Authentication
- Submit Document API
- Get Document API
- Search Documents API
- Cancel Document API
- Get Recent Documents API
- Token Management (Access/Refresh/Expiration)

### 📄 إدارة الفواتير
- إنشاء فواتير B2B و B2C
- حسابات تلقائية (Subtotal, Discount, Tax, Net Total)
- توقيع إلكتروني
- متابعة حالة الفاتورة (Valid, Invalid, Rejected, Submitted)
- مزامنة تلقائية كل 5-10 دقائق

### 🧮 الحسابات الضريبية
```
Total = Subtotal - Discount + Tax
Tax = (Subtotal - Discount) × Tax Rate
Default VAT Rate: 14%
```

### 📊 Dashboard احترافي
- عدد الفواتير الكلي
- الفواتير الصالحة
- الفواتير المرفوضة
- إجمالي المبيعات
- مبيعات الشهر الحالي

### 🔄 إدارة الأصناف
- Item Master لكل شركة
- دعم GS1 Code و Internal Code
- أنواع الضرائب (VAT / Table Tax)
- استيراد جماعي

### 🔒 الأمان
- تشفير API Keys
- Audit Trail لكل العمليات
- عدم التعديل بعد الإرسال لـ ETA

---

## 🛠 المتطلبات

- PHP >= 8.2
- Composer
- SQLite/MySQL/PostgreSQL
- Laravel 12.x

---

## 📦 التثبيت

```bash
# استنساخ المشروع
cd /workspace/finovate-ahmed-eg

# تثبيت المكتبات
composer install

# نسخ ملف البيئة
cp .env.example .env

# إنشاء مفتاح التطبيق
php artisan key:generate

# تشغيل الترحيلات
php artisan migrate

# (اختياري) تشغيل Seeders للبيانات التجريبية
php artisan db:seed

# تشغيل السيرفر المحلي
php artisan serve
```

---

## 🏗 الهيكلية

```
finovate-ahmed-eg/
├── app/
│   ├── Models/
│   │   ├── Company.php          # نموذج الشركة
│   │   ├── Invoice.php          # نموذج الفاتورة
│   │   ├── InvoiceItem.php      # نموذج أصناف الفاتورة
│   │   ├── Item.php             # نموذج الصنف
│   │   ├── EtaToken.php         # نموذج توكن ETA
│   │   └── AuditLog.php         # نموذج سجل التدقيق
│   ├── Services/
│   │   ├── EtaAuthService.php   # خدمة المصادقة مع ETA
│   │   └── EtaInvoiceService.php # خدمة الفواتير ETA
│   └── Http/Controllers/Api/
│       ├── CompanyController.php
│       ├── InvoiceController.php
│       └── ItemController.php
├── database/migrations/
│   └── 2024_01_01_000001_create_finovate_tables.php
├── routes/
│   └── api.php                  # مسارات API
└── README.md
```

---

## 🌐 API Endpoints

### Companies (إدارة الشركات)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/companies` | قائمة الشركات |
| POST | `/api/v1/companies` | إضافة شركة جديدة |
| GET | `/api/v1/companies/{id}` | تفاصيل الشركة |
| PUT | `/api/v1/companies/{id}` | تحديث الشركة |
| POST | `/api/v1/companies/{id}/test-connection` | اختبار الاتصال بـ ETA |
| GET | `/api/v1/companies/{id}/stats` | إحصائيات الشركة |

### Items (إدارة الأصناف)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/companies/{id}/items` | قائمة الأصناف |
| POST | `/api/v1/companies/{id}/items` | إضافة صنف جديد |
| GET | `/api/v1/companies/{id}/items/{itemId}` | تفاصيل الصنف |
| PUT | `/api/v1/companies/{id}/items/{itemId}` | تحديث الصنف |
| DELETE | `/api/v1/companies/{id}/items/{itemId}` | حذف/تعطيل الصنف |
| POST | `/api/v1/companies/{id}/items/bulk-import` | استيراد جماعي |

### Invoices (إدارة الفواتير)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/companies/{id}/invoices` | قائمة الفواتير |
| POST | `/api/v1/companies/{id}/invoices` | إنشاء فاتورة جديدة |
| GET | `/api/v1/companies/{id}/invoices/{invoiceId}` | تفاصيل الفاتورة |
| POST | `/api/v1/companies/{id}/invoices/{invoiceId}/submit` | إرسال لـ ETA |
| POST | `/api/v1/companies/{id}/invoices/{invoiceId}/sync` | مزامنة الحالة |
| POST | `/api/v1/companies/{id}/invoices/{invoiceId}/cancel` | إلغاء الفاتورة |
| GET | `/api/v1/companies/{id}/invoices/stats` | إحصائيات الفواتير |

---

## 💡 استخدام النظام

### 1. إضافة شركة جديدة

```json
POST /api/v1/companies
{
    "name": "شركة المثال",
    "tax_registration_number": "123-456-789",
    "eta_client_id": "your-client-id",
    "eta_client_secret": "your-client-secret",
    "digital_certificate": "path/to/certificate",
    "certificate_password": "cert-password",
    "settings": {
        "address": "العنوان",
        "phone": "0123456789",
        "email": "info@example.com"
    }
}
```

### 2. إضافة صنف

```json
POST /api/v1/companies/1/items
{
    "code": "ITEM001",
    "name": "منتج تجريبي",
    "description": "وصف المنتج",
    "unit_price": 100.00,
    "tax_type": "VAT",
    "tax_rate": 14.00,
    "unit_type": "EA",
    "is_active": true
}
```

### 3. إنشاء فاتورة

```json
POST /api/v1/companies/1/invoices
{
    "invoice_number": "INV-2024-001",
    "invoice_type": "B2B",
    "issue_date": "2024-01-15",
    "delivery_date": "2024-01-16",
    "buyer_name": "العميل الأول",
    "buyer_tax_id": "987-654-321",
    "buyer_vat_number": "VAT123456",
    "buyer_address": "عنوان العميل",
    "buyer_email": "client@example.com",
    "buyer_phone": "0109876543",
    "items": [
        {
            "item_code": "ITEM001",
            "item_name": "منتج تجريبي",
            "quantity": 10,
            "unit_price": 100.00,
            "discount_percent": 5,
            "tax_rate": 14.00,
            "tax_type": "VAT",
            "unit_type": "EA"
        }
    ]
}
```

### 4. إرسال الفاتورة لـ ETA

```json
POST /api/v1/companies/1/invoices/{invoiceId}/submit
```

---

## 🔐 الأمان

### تشفير البيانات الحساسة
- `eta_client_secret` - مشفر باستخدام Laravel Encrypter
- `certificate_password` - مشفر باستخدام Laravel Encrypter

### Audit Trail
يتم تسجيل جميع العمليات:
- إنشاء/تحديث/حذف الفواتير
- إرسال الفواتير لـ ETA
- مزامنة الحالات
- تسجيل الدخول والخروج

### صلاحيات الوصول
- كل شركة لها بيانات معزولة
- لا يمكن تعديل الفاتورة بعد إرسالها لـ ETA
- التحقق من صحة البيانات قبل الإرسال

---

## 📝 حالات الفاتورة

| الحالة | الوصف |
|--------|-------|
| DRAFT | مسودة - لم ترسل بعد |
| SUBMITTED | تم الإرسال لـ ETA |
| VALID | مقبولة من ETA |
| INVALID | مرفوضة من ETA |
| REJECTED | مرفوضة نهائياً |
| CANCELLED | ملغاة |

---

## 🔄 المزامنة التلقائية

يتم تنفيذ Sync تلقائي للفواتير كل 5-10 دقائق لتحديث الحالات من ETA.

لإعداد Job للمزامنة:

```bash
php artisan make:job SyncInvoicesWithEta
```

---

## 📞 الدعم الفني

للأسئلة والمشاكل التقنية، يرجى التواصل مع فريق التطوير.

---

## 📄 الترخيص

جميع الحقوق محفوظة © 2024 Finovate – AHMED EG

---

**تم التطوير بواسطة:** Finovate Team  
**الإصدار:** 1.0.0  
**تاريخ آخر تحديث:** 2024
