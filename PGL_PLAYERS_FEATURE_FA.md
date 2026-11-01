# بخش بازیکنان پچ PGL — راهنمای نصب و استفاده (۱۴۰۵/۰۸/۰۹ — نسخه ۱.۳)

صفحه‌های عمومی، ایمپورت دیتابیس پچ، نام‌های دوزبانه و ترکیب تیم روی لندینگ —
کامل و تست‌شده به‌صورت لوکال.
**۱.۳:** صفحه‌ی ادمین «نام‌های بازیکنان پچ» حذف شد — نام فارسی فقط از فایل
`--fa-names` موقع ایمپورت داده می‌شود (ستون name_fa و نمایش دوزبانه می‌ماند).
**۱.۲:** ستون name_fa + سوییچ --fa-names + GetPglSquadAction + بلوک ترکیب
خودکار روی صفحه‌ی تیم لندینگ (از طریق `pgl_team_id` در کاتالوگ).
**۱.۱:** نظرات بازیکنان کامل حذف شد.

## صفحات

| مسیر | توضیح |
|---|---|
| `/players` | هاب: ستاره‌های پچ (بیشترین اورال)، تیم‌های ایرانی، آخرین تغییرات، جستجوی همه‌ی ۷۹۶ تیم |
| `/players/t/{teamId}` | صفحه‌ی تیم: ترکیب مرتب‌شده با اورال + نشان تغییرات آخرین آپدیت (مثل `79 ▲ 82`) و برچسب «جدید» |
| `/players/p/{pid}` | صفحه‌ی بازیکن: اورال، مشخصات، تیم‌ها، ۲۵ آمار در ۵ گروه با نوار، تاریخچه‌ی کامل تغییرات |
| `/{landing}/leagues/{league}/{team}` | ترکیب همان تیم روی صفحه‌ی تیم لندینگ (نیازمند پک لندینگ ≥ ۱.۱ + pgl_team_id در کاتالوگ) |

## نصب روی سرور

```bash
php artisan migrate            # مایگریشن pgl_players_tables
php artisan optimize:clear
```

ایمپورت اولیه و بعد از هر آپدیت پچ:

```bash
# خروجی پارسر (E:\FIFA 23\pgl6-db-parser\pgl6_db_export.py) را روی سرور کپی کنید
php artisan pgl:import-players /path/to/pgl6_export_v2.json --label="آپدیت 6.4"
```

- ایمپورت، تفاوت‌ها را با وضعیت قبلی مقایسه و در `pgl_player_changes` ثبت
  می‌کند (اورال/پست/سن/قد/وزن/انتقال/هر آمار) — همین رکوردها نشان‌ها و
  تاریخچه‌ی صفحه‌ها را می‌سازند.
- کارت‌های چندگانه‌ی یک بازیکن (کلاسیک/ویژه) روی یک «نمونه‌ی اصلی» جمع
  می‌شوند (`is_primary`/`primary_pid`).
- نام فارسی: فقط از فایل `--fa-names` (ساختار
  `{"players":{pid:fa},"teams":{teamId:fa}}`)؛ در ایمپورت‌های بعدی حفظ می‌شود.
- ترکیب روی لندینگ: در پنل کاتالوگ، فیلد «شناسه تیم در دیتابیس پچ» هر تیم را
  پر کنید؛ بلوک ترکیب (دوزبانه، با نشان تغییرات) خودکار روی صفحه‌ی تیم
  لندینگ می‌آید و با هر ایمپورت تازه می‌شود.
- ⚠️ نام تیم‌های Team.bin پچ لزوماً با ترکیب همان شناسه نمی‌خواند (اسلات
  ۴۹۵۱ با نام «Peru» = پرسپولیس واقعی). شناسه را با دیدن ترکیب در
  `/players/t/{id}` انتخاب کنید.

## گردش کار آپدیت پچ (جمع‌بندی کل زنجیره)

1. ادیت دیتابیس پچ با ابزارهای خودتان (همان همیشه).
2. خروجی CSV از `Pes 2020 Editor` (برای اورال دقیق) + کپی فایل‌های pesdb.
3. `python pgl6_db_export.py export <پوشه pesdb> out.json "Players - PES 2021 - Bin.csv"`
4. آپلود `out.json` روی سرور و اجرای `php artisan pgl:import-players out.json --label="..."`.
5. تمام — تغییرات خودکار روی صفحات دیده می‌شوند.

## فایل‌های اضافه/تغییرکرده

```
database/migrations/2026_10_31_020001_create_pgl_players_tables.php   (جدید)
app/Domain/Players/Models/{PglPlayer,PglTeam,PglImport,PglPlayerChange}.php  (جدید)
app/Console/Commands/ImportPglPlayers.php                              (جدید)
app/Http/Controllers/Front/Players/*.php                               (جدید ×۳)
routes/front/players.php  +  یک خط require در routes/front.php (قبل از content.php)
resources/views/front/players/{hub,team,player}.blade.php              (جدید)
```

تنها ادغام دستی لازم: خط `require __DIR__.'/front/players.php';` در
`routes/front.php` **قبل از** خط `content.php` (به‌خاطر روتر کچ‌آل CMS).

## اگر قبلاً نسخه‌ی ۱.۰ (با نظرات) نصب شده بود

```bash
php artisan db:table --drop=player_comments   # یا:
# mysql: DROP TABLE player_comments;
```
- فایل‌های زیر را حذف کنید: `app/Domain/Players/Models/PlayerComment.php`،
  `app/Http/Controllers/Front/Players/StorePlayerCommentController.php`،
  `app/Http/Controllers/Admin/Players/PlayerCommentsController.php`،
  `resources/views/admin/players/comments.blade.php`، `routes/admin/players.php`
- در `routes/admin.php` خط `require __DIR__ . '/admin/players.php';` را بردارید.
- در `config/admin-sidebar.php` آیتم «نظرهای بازیکنان پچ» و در
  `resources/views/admin/partials/sidebar.blade.php` بلوک
  `$sidebarBadges['pending_player_comments']` را حذف کنید.

## نکته‌ی محیط تست

کش سرور باید taggable باشد (redis مثل محیط فعلی تولید). روی محیط‌های لوکال با
`CACHE_STORE=file/database` فوتر خود سایت (`GetFooterStatsAction`) خطای
«This cache store does not support tagging» می‌دهد — مربوط به این بخش نیست.
تست‌های من با اورراید موقت کش انجام شد و همه‌ی صفحات سبز بود.
