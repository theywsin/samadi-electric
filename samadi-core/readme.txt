=== Samadi Core ===
Contributors: samadielectric
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Core features of the Samadi Electric theme, kept in a plugin so they survive a theme switch: wishlist, product comparison, SMS newsletter sign-up, Jalali dates, Iranian checkout validation, store schema and light security hardening.

== Description ==

Every module can be switched off in «صمدی ← تنظیمات».

* Wishlist: guest list in a cookie, account list in user meta, merged on login. Connects to the theme's card heart, header icon, mobile bottom bar and My Account wishlist panel. Steps aside when YITH or TI WooCommerce Wishlist is active. Shortcode: [samadi_wishlist].
* Compare: up to 2–6 products side by side, "differences only" switch, floating compare bar. Shortcode: [samadi_compare].
* Newsletter: mobile-number form ([samadi_newsletter]), subscriber list with search, delete and CSV export («صمدی ← اعضای خبرنامه»). Action `samadi_core_newsletter_subscribed` for SMS integrations.
* Jalali dates: post, comment, order and review dates in the Solar Hijri calendar; machine formats stay Gregorian. Steps aside when Parsi Date is active.
* Iranian checkout (classic checkout and My Account addresses): Persian/Arabic digits converted, mobile, 10-digit postcode and national code (کد ملی) validated for Iranian addresses; optional national code field saved on the order.
* Store schema: Store + WebSite/SearchAction JSON-LD on the home page from the theme's contact settings; off when Yoast, Rank Math, SEOPress or AIOSEO is active.
* Security: XML-RPC off, WordPress version hidden, user-name discovery blocked, one login error message.
* Works with other themes (falls back to its own button placement and styles). HPOS compatible.

Templates can be overridden by copying templates/*.php to yourtheme/samadi-core/.

Bundled: Lucide icons subset (ISC) — includes/LICENSE-lucide.txt

== Changelog ==

= 1.0.0 =
* Initial release.
