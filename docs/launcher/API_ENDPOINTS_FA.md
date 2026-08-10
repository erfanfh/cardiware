# قرارداد API لانچر

Base URL نمونه: `https://example.com/api/v1`

تمام endpointهای خصوصی هدرهای زیر را لازم دارند:

```http
Authorization: Bearer <token>
Accept: application/json
X-Launcher-Version: 1.0.0
```

## احراز هویت و دستگاه

### `POST /auth/login`

```json
{
  "login": "user@example.com",
  "password": "secret",
  "device": {
    "id": "7e02c735-794f-4c63-ae2e-98dbf5d1d4ab",
    "name": "Desktop PC",
    "fingerprint_hash": "64-lowercase-hex-characters",
    "platform": "windows",
    "app_version": "1.0.0"
  }
}
```

`device.id` باید در همان نصب لانچر ثابت بماند و `fingerprint_hash` باید SHA-256
اثر انگشت پایدار سخت‌افزار باشد. خطاهای مهم: `device_limit_reached`,
`device_fingerprint_mismatch`, `device_revoked`.

- `POST /auth/logout`
- `GET /me`
- `GET /devices`
- `DELETE /devices/{deviceUuid}`

## Bootstrap و Catalog

- `GET /launcher/bootstrap` — نسخه حداقل، آخرین نسخه، تعمیرات و URL رسمی لانچر.
- `GET /launcher/catalog?search=&page=&per_page=18`
- `GET /launcher/catalog/{saleItemId}`

Catalog برای هر نسخه فروش مالکیت و action مناسب را برمی‌گرداند:
`can_buy`، `can_claim` و `can_install`.

تصویر نمایشی هر آیتم لانچر فقط با فیلد `hero_url` برگردانده می‌شود. این تصویر
مستقیماً از Media انتخاب‌شده برای همان `sale_item` خوانده می‌شود و هیچ fallbackای
به تصویر اصلی Product یا نسخه فروش دیگری ندارد. اگر Hero برای آن نسخه انتخاب نشده
باشد مقدار `hero_url` برابر `null` است. فیلدهای `cover` و `cover_url` جزو قرارداد
لانچر نیستند.

## خرید و محصول رایگان

- `POST /launcher/sale-items/{id}/claim`
  - Header اجباری: `Idempotency-Key: <UUID>`
- `POST /launcher/sale-items/{id}/checkout`

```json
{"return_uri":"mwlauncher://purchase-complete"}
```

لانچر `browser_url` را در مرورگر باز می‌کند. پس از پرداخت موفق، سایت
`mwlauncher://purchase-complete?state=...` را باز می‌کند.

- `POST /launcher/checkout/consume`

```json
{"state":"state-returned-by-checkout"}
```

پس از consume موفق، Library مجدداً بارگیری شود.

## Library و Manifest پایه

- `GET /library`
- `GET /library/{accessId}`
- `GET /library/{accessId}/manifest`

Manifest شامل شناسه فایل‌ها، حجم، SHA-256، ترتیب پارت‌ها، نام پوشه نصب،
OPTION FILE و تنظیمات اجرای بازی/Sider است؛ URL واقعی فایل را شامل نمی‌شود.

برای هر فایل پایه:

`POST /library/{accessId}/files/{fileId}/download`

```json
{"download_session_id":"c0429807-f641-47b5-a619-9c14ac858d97"}
```

UUID یک عملیات دانلود در همه retryها ثابت بماند؛ در این صورت سهمیه دوباره کم
نمی‌شود. URL برگشتی کوتاه‌عمر و امضاشده است.

## آپدیت و مادمنیجر

- `GET /library/{accessId}/updates`
- `GET /library/{accessId}/mods`

دانلود فایل آپدیت یا ماد:

`POST /library/{accessId}/{update|mod}/{contentId}/files/{fileId}/download`

```json
{"download_session_id":"77bb182b-ad95-44ae-a55e-a4578d479bb7"}
```

گروه ماد با `is_exclusive=true` یعنی در هر لحظه فقط یک ماد آن گروه فعال باشد.
WPF قبل از نصب ماد جدید، وضعیت ماد قبلی همان گروه را خاموش می‌کند؛ جایگزینی
فیزیکی فایل با merge/replace بسته جدید انجام می‌شود.

## سیاست فایل و امنیت

- Source URL فقط HTTPS است.
- بهتر است `LAUNCHER_ALLOWED_DOWNLOAD_HOSTS` حتماً مقدار داشته باشد.
- URL اصلی فقط پشت redirect امضاشده و کوتاه‌عمر قرار می‌گیرد.
- لانچر بعد از دانلود باید اندازه و SHA-256 را پیش از استخراج کنترل کند.
- مسیرهای داخل archive باید در برابر path traversal کنترل شوند.
- خروجی API در خطاهای دامنه دارای `message` و `code` پایدار است.
