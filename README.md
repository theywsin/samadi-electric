# صمدی الکتریک — قالب وردپرس + افزونه همراه

قالب اختصاصی، فارسی و راست‌چین فروشگاه «صمدی الکتریک» برای WooCommerce و Elementor.

| بسته | مسیر | خروجی نصب |
|---|---|---|
| قالب **Samadi Electric** | `samadi-electric/` | `dist/samadi-electric.zip` |
| افزونه همراه **Samadi Elementor Widgets** | `samadi-elementor-widgets/` | `dist/samadi-elementor-widgets.zip` |
| افزونه هسته **Samadi Core** (راه‌انداز + دموی کامل) | `samadi-core/` | `dist/samadi-core.zip` |

## نصب آسان با دموی کامل

فایل آماده: [`release/samadi-electric-full-package.zip`](release/samadi-electric-full-package.zip) — راهنما: [نصب سریع](docs/00-quick-install.md).

قالب را نصب و فعال کنید ← در اعلان «نصب و فعال‌سازی Samadi Core» را بزنید ← «شروع راه‌اندازی کامل».
WooCommerce، Elementor و افزونه همراه نصب و همه صفحات، ۲۷ محصول، دسته‌ها، برندها، وبلاگ، منوها و تنظیمات درون‌ریزی می‌شوند؛ نیازی به ساختن دستی هیچ صفحه‌ای نیست.

ساخت فایل‌های ZIP:

```bash
./bin/build.sh
```

(پوشه `dist/` در گیت نیست؛ با دستور بالا ساخته می‌شود.)

## مستندات

0. [نصب سریع با دموی کامل](docs/00-quick-install.md)
1. [راهنمای نصب](docs/01-installation.md)
2. [ساخت و تخصیص قالب‌های Elementor](docs/02-elementor.md)
3. [افزونه‌های ضروری و اختیاری](docs/03-plugins.md)
4. [تنظیمات WooCommerce](docs/04-woocommerce.md)
5. [ورود محصولات و ویژگی‌های فنی](docs/05-products.md)
6. [چک‌لیست آزمون فروشگاه](docs/06-test-checklist.md)
7. [محدودیت‌ها و قابلیت‌های وابسته به افزونه پولی](docs/07-limitations.md)
8. [گزارش تست‌های انجام‌شده](docs/08-test-report.md)
9. [ممیزی طراحی و اصلاحات مطابق مرجع](docs/09-design-audit.md)

## ساختار

```
samadi-electric/                 قالب
  inc/                           ماژول‌ها: setup, customizer, enqueue, elementor, woocommerce, seo, performance, accessibility …
  template-parts/                هدر، فوتر، صفحه اصلی، کارت‌ها
  woocommerce/                   فقط content-product.php و archive-product.php (با حفظ همه هوک‌ها)
  page-templates/                تمام‌عرض و صفحه فرود
  assets/{css,js,fonts,images}   بدون jQuery و بدون CDN؛ فونت وزیرمتن محلی
samadi-elementor-widgets/        افزونه همراه
  includes/                      فیلتر محصولات، جستجوی زنده، استعلام قیمت/خرید عمده، محصولات اخیر، ابزار راه‌اندازی
  includes/elementor/            ۱۹ ویجت + ۵ برچسب پویا
  templates/elementor/           قالب‌های آماده صفحه اصلی، هدر و فوتر (JSON)
samadi-core/                     افزونه هسته: راه‌انداز، نصب افزونه‌ها، درون‌ریزی/حذف دمو، WP-CLI (wp samadi)
  demo/                          محتوای دمو (content.php) و تصاویر تولیدشده
bin/                             build.sh، make-pot.php، make-demo-images.py
```
