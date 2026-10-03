# SSE — استيراد المستخدمين من Excel/CSV

تطبيق Laravel لرفع ملفات Excel أو CSV واستيراد المستخدمين في الخلفية عبر الطوابير (Queue)، مع متابعة التقدّم على صفحة النتائج وواجهة JSON اختيارية، بالإضافة إلى بث **Server-Sent Events (SSE)** لتتبّع الصفوف لحظياً.

واجهة المستخدم بالعربية (RTL) ومبنية بـ Tailwind CSS.

## الميزات

- رفع ملفات **CSV / Excel** (Maatwebsite Excel).
- معالجة **غير متزامنة** عبر `ShouldQueue` مع قراءة على دفعات (chunk size: 100).
- تسجيل كل عملية استيراد في جدول `imports` مع عدّادات النجاح والفشل.
- تفاصيل لكل صف في `import_results` (نجاح أو فشل مع رسائل التحقق).
- صفحة نتائج تُحدّث نفسها كل ثانيتين عبر **polling** حتى اكتمال الاستيراد.
- endpoint اختياري لبث التقدّم عبر **SSE** (`?stream=1`).

## المتطلبات

- PHP **8.3+**
- Composer
- Node.js و npm
- MySQL (أو تعديل `.env` لقاعدة بيانات أخرى)
- **Worker للطابور** — الاستيراد لا يكتمل بدون تشغيل معالج الطوابير

## التثبيت

```bash
composer setup
```

أو يدوياً:

```bash
composer install
cp .env.example .env
php artisan key:generate
# عدّل إعدادات DB في .env
php artisan migrate
npm install
npm run build
```

### إعداد `.env` المهم

| المتغير | الوصف |
|---------|--------|
| `DB_*` | اتصال قاعدة البيانات (الافتراضي: MySQL، قاعدة `sse`) |
| `QUEUE_CONNECTION` | `database` (مطلوب للاستيراد في الخلفية) |

بعد أول `migrate` تأكد من وجود جداول `jobs` و`failed_jobs` (تُنشأ مع migrations الافتراضية).

## التشغيل

في نافذة طرفية:

```bash
composer run dev
```

أو:

```bash
php artisan serve
npm run dev
```

في **نافذة ثانية** (ضروري):

```bash
php artisan queue:work
```

بدون `queue:work` يبقى الاستيراد في حالة `PENDING` ولا تُعالَج الصفوف.

## الاستخدام

| المسار | الطريقة | الاسم | الوصف |
|--------|---------|-------|--------|
| `/import-users` | GET | `users.import` | صفحة رفع الملف |
| `/import` | POST | `import` | بدء الاستيراد (يرجع `import_id` JSON) |
| `/imports/{import}` | GET | `imports.show` | صفحة النتائج + polling |

### تنسيق الملف

الصف الأول **عناوين أعمدة** (heading row). الأعمدة المطلوبة:

| name | email | password |
|------|-------|----------|
| نص | بريد فريد | كلمة مرور (تُخزَّن م hashed في `users`) |

قواعد التحقق (لكل صف):

- `name`: مطلوب، نص
- `email`: مطلوب، بريد صالح، **فريد** في جدول `users`
- `password`: مطلوب

الصفوف الفاشلة لا توقف الملف بالكامل؛ تُسجَّل في `import_results` وتظهر في جدول الأخطاء.

### تدفق العمل

1. المستخدم يرفع الملف من `/import-users`.
2. `POST /import` ينشئ سجل `Import`، يحسب `total_records`، ويضع مهمة Excel في الطابور.
3. يُوجَّه المتصفح إلى `/imports/{id}`.
4. الصفحة تطلب `GET /imports/{id}` مع `Accept: application/json` كل 2 ثانية حتى `status === COMPLETE`.
5. `UsersImport` ينشئ مستخدماً لكل صف ناجح، أو يسجّل الفشل في `import_results`.

## واجهات API

### بدء الاستيراد

```http
POST /import
Content-Type: multipart/form-data
Accept: application/json

file=<الملف>
```

**استجابة:**

```json
{
  "import_id": 1
}
```

### حالة الاستيراد (JSON)

```http
GET /imports/{import}
Accept: application/json
```

**استجابة (مختصرة):**

```json
{
  "import_id": 1,
  "total_records": 100,
  "processed_records": 42,
  "total_success": 40,
  "total_faild": 2,
  "status": 1,
  "completed": false,
  "failed_results": []
}
```

`status`: `1` = `PENDING`، `2` = `COMPLETE` (انظر `App\ImportStatusEnum`).

### بث SSE (اختياري)

```http
GET /imports/{import}?stream=1
```

يرسل أحداث `data:` لكل صف جديد يُعالَج، ثم حدث `completed` عند الانتهاء. الواجهة الحالية تعتمد على polling وليس EventSource.

## هيكل المشروع (الملفات الأساسية)

```
app/
├── Http/Controllers/User/ImportController.php   # رفع، JSON، SSE
├── Imports/UsersImport.php                      # منطق الاستيراد + Queue
├── Models/Import.php
├── Models/ImportResult.php
├── ImportStatusEnum.php
└── ImportResultStatusEnum.php

resources/views/users/
├── import.blade.php       # صفحة الرفع
└── show_import.blade.php  # النتائج + polling

routes/web.php
```

## قاعدة البيانات

- **`users`**: المستخدمون المستوردون.
- **`imports`**: ملف، حجم، إجمالي الصفوف، المعالَج، نجاح/فشل، `status`.
- **`import_results`**: `import_id`, `row`, `status`, `values`, `errors` (JSON).
- **`jobs`**: مهام الطابور (Maatwebsite Excel queued import).

## الاختبارات

```bash
composer test
# أو
php artisan test --compact
```

## التقنيات

- [Laravel 13](https://laravel.com/docs)
- [Maatwebsite Excel 4](https://docs.laravel-excel.com/)
- [Tailwind CSS 4](https://tailwindcss.com/) + Vite
- Pest (اختبارات)

## الترخيص

MIT — انظر ترخيص Laravel في [laravel.com](https://laravel.com).
