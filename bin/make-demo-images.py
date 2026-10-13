#!/usr/bin/env python3
"""Generate the illustrated demo images shipped with Samadi Core.

All images are original illustrations drawn here (no stock photos, no real brand marks),
so the demo can be redistributed freely. Usage:

    python3 bin/make-demo-images.py samadi-core/demo/images
"""
import math
import os
import sys

from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FONT = os.path.join(ROOT, 'samadi-electric/assets/fonts/vazirmatn/vazirmatn-arabic-wght-normal.woff2')
FONT_LATIN = os.path.join(ROOT, 'samadi-electric/assets/fonts/vazirmatn/vazirmatn-latin-wght-normal.woff2')

PRIMARY = (6, 78, 59)
BRAND = (7, 94, 70)
ACCENT = (199, 245, 42)
BG = (247, 248, 246)
TEXT = (21, 36, 33)

S = 2  # supersampling factor


def font(size, weight=800, latin=False):
    f = ImageFont.truetype(FONT_LATIN if latin else FONT, size * S, layout_engine=ImageFont.Layout.RAQM)
    f.set_variation_by_axes([weight])
    return f


def canvas(w, h, color=(255, 255, 255)):
    return Image.new('RGBA', (w * S, h * S), color + (255,))


def finish(img, path, w, h, quality=86):
    img = img.convert('RGB').resize((w, h), Image.LANCZOS)
    img.save(path, 'JPEG', quality=quality, optimize=True, progressive=True)


def shadow(img, box, radius, blur=28, offset=18, alpha=46):
    """Soft drop shadow under a rounded box (box in 1x units)."""
    layer = Image.new('RGBA', img.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(layer)
    x0, y0, x1, y1 = [v * S for v in box]
    d.rounded_rectangle((x0, y0 + offset * S, x1, y1 + offset * S), radius * S, fill=(21, 36, 33, alpha))
    layer = layer.filter(ImageFilter.GaussianBlur(blur * S))
    img.alpha_composite(layer)


def rr(d, box, radius, fill=None, outline=None, width=1):
    d.rounded_rectangle([v * S for v in box], radius * S, fill=fill, outline=outline, width=width * S)


def ell(d, box, fill=None, outline=None, width=1):
    d.ellipse([v * S for v in box], fill=fill, outline=outline, width=width * S)


def gradient(w, h, top, bottom):
    g = Image.new('RGBA', (w * S, h * S))
    d = ImageDraw.Draw(g)
    for y in range(h * S):
        t = y / (h * S - 1)
        c = tuple(int(top[i] + (bottom[i] - top[i]) * t) for i in range(3))
        d.line([(0, y), (w * S, y)], fill=c + (255,))
    return g


def backdrop(w=800, h=800):
    """Product photo backdrop: light studio gradient with floor glow."""
    img = gradient(w, h, (252, 253, 251), (236, 240, 236))
    glow = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(glow).ellipse((w * S * 0.12, h * S * 0.72, w * S * 0.88, h * S * 0.92), fill=(21, 36, 33, 26))
    img.alpha_composite(glow.filter(ImageFilter.GaussianBlur(30 * S)))
    return img


# ---------------------------------------------------------------- products

def plate(img, color, box=(200, 200, 600, 600)):
    shadow(img, box, 56)
    d = ImageDraw.Draw(img)
    rr(d, box, 56, fill=color + (255,))
    x0, y0, x1, y1 = box
    # bevel highlight
    rr(d, (x0 + 10, y0 + 10, x1 - 10, y1 - 10), 48, outline=(255, 255, 255, 90), width=3)
    return d


def darker(c, k=0.82):
    return tuple(max(0, int(v * k)) for v in c)


def draw_switch(color=(250, 250, 248), gangs=1):
    img = backdrop()
    d = plate(img, color)
    inner = darker(color, 0.93)
    if gangs == 1:
        boxes = [(270, 270, 530, 530)]
    else:
        boxes = [(260, 270, 395, 530), (405, 270, 540, 530)]
    for b in boxes:
        rr(d, b, 26, fill=inner + (255,))
        x0, y0, x1, y1 = b
        rr(d, (x0 + 8, y0 + 8, x1 - 8, (y0 + y1) / 2), 20, fill=color + (255,))
        rr(d, ((x0 + x1) / 2 - 18, y1 - 34, (x0 + x1) / 2 + 18, y1 - 26), 4, fill=ACCENT + (255,))
    return img


def draw_socket(color=(250, 250, 248), usb=False):
    img = backdrop()
    d = plate(img, color)
    inner = darker(color, 0.94)
    ell(d, (275, 275, 525, 525), fill=inner + (255,))
    ell(d, (290, 290, 510, 510), fill=darker(color, 0.97) + (255,))
    hole = darker(color, 0.35) + (255,)
    ell(d, (330, 378, 370, 418), fill=hole)
    ell(d, (430, 378, 470, 418), fill=hole)
    rr(d, (385, 285, 415, 315), 6, fill=(190, 196, 192, 255))
    rr(d, (385, 485, 415, 515), 6, fill=(190, 196, 192, 255))
    if usb:
        rr(d, (345, 548, 395, 568), 4, fill=hole)
        rr(d, (405, 548, 455, 568), 8, fill=hole)
    return img


def draw_bulb(glow=True):
    img = backdrop()
    if glow:
        g = Image.new('RGBA', img.size, (0, 0, 0, 0))
        ImageDraw.Draw(g).ellipse([v * S for v in (190, 120, 610, 540)], fill=ACCENT + (80,))
        img.alpha_composite(g.filter(ImageFilter.GaussianBlur(50 * S)))
    shadow(img, (300, 520, 500, 660), 40, blur=20, offset=10)
    d = ImageDraw.Draw(img)
    ell(d, (250, 170, 550, 470), fill=(255, 255, 255, 255), outline=(225, 230, 226, 255), width=3)
    d.polygon([(285 * S, 410 * S), (515 * S, 410 * S), (470 * S, 520 * S), (330 * S, 520 * S)], fill=(255, 255, 255, 255))
    ell(d, (300, 200, 400, 300), fill=(255, 255, 255, 255))
    rr(d, (335, 510, 465, 600), 14, fill=(196, 200, 197, 255))
    for y in range(525, 595, 18):
        rr(d, (330, y, 470, y + 8), 4, fill=(170, 176, 172, 255))
    ell(d, (370, 590, 430, 640), fill=(90, 98, 94, 255))
    return img


def draw_panel(round_=False):
    img = backdrop()
    box = (170, 250, 630, 560)
    if round_:
        shadow(img, (210, 210, 590, 590), 190)
        d = ImageDraw.Draw(img)
        ell(d, (210, 210, 590, 590), fill=(232, 236, 233, 255))
        ell(d, (232, 232, 568, 568), fill=(255, 255, 250, 255))
        g = Image.new('RGBA', img.size, (0, 0, 0, 0))
        ImageDraw.Draw(g).ellipse([v * S for v in (260, 260, 540, 540)], fill=(255, 255, 220, 140))
        img.alpha_composite(g.filter(ImageFilter.GaussianBlur(30 * S)))
        return img
    shadow(img, (220, 220, 580, 580), 24)
    d = ImageDraw.Draw(img)
    rr(d, (220, 220, 580, 580), 24, fill=(232, 236, 233, 255))
    rr(d, (240, 240, 560, 560), 14, fill=(255, 255, 250, 255))
    g = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(g).rounded_rectangle([v * S for v in (270, 270, 530, 530)], 30 * S, fill=(255, 255, 220, 150))
    img.alpha_composite(g.filter(ImageFilter.GaussianBlur(30 * S)))
    return img


def draw_spot():
    img = backdrop()
    shadow(img, (250, 250, 550, 550), 150)
    d = ImageDraw.Draw(img)
    ell(d, (250, 250, 550, 550), fill=(245, 246, 244, 255), outline=(220, 224, 221, 255), width=4)
    ell(d, (300, 300, 500, 500), fill=(60, 66, 63, 255))
    ell(d, (335, 335, 465, 465), fill=(255, 252, 225, 255))
    g = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(g).ellipse([v * S for v in (340, 340, 460, 460)], fill=(255, 255, 200, 200))
    img.alpha_composite(g.filter(ImageFilter.GaussianBlur(14 * S)))
    return img


def draw_cable(color=(220, 40, 40)):
    img = backdrop()
    shadow(img, (200, 260, 600, 600), 170, blur=26)
    d = ImageDraw.Draw(img)
    cx, cy = 400, 420
    for i, r in enumerate(range(200, 70, -12)):
        c = darker(color, 0.75 + 0.25 * ((i % 2)))
        ell(d, (cx - r, cy - r * 0.86, cx + r, cy + r * 0.86), outline=c + (255,), width=12)
    ell(d, (cx - 64, cy - 55, cx + 64, cy + 55), fill=(244, 245, 243, 255))
    rr(d, (cx + 150, cy - 230, cx + 172, cy - 120), 8, fill=color + (255,))
    rr(d, (cx + 154, cy - 250, cx + 168, cy - 226), 4, fill=(205, 140, 60, 255))
    return img


def draw_spool(color=(30, 90, 200)):
    img = backdrop()
    shadow(img, (220, 200, 580, 620), 40)
    d = ImageDraw.Draw(img)
    rr(d, (240, 200, 560, 250), 20, fill=(70, 74, 72, 255))
    rr(d, (240, 570, 560, 620), 20, fill=(70, 74, 72, 255))
    rr(d, (275, 245, 525, 575), 10, fill=color + (255,))
    for y in range(255, 570, 14):
        d.line([(280 * S, y * S), (520 * S, (y + 6) * S)], fill=darker(color, 0.8) + (255,), width=4 * S)
    return img


def draw_breaker(poles=1, rcd=False):
    img = backdrop()
    w = 110 * poles + (40 if rcd else 0)
    x0 = 400 - w / 2
    box = (x0, 190, x0 + w, 610)
    shadow(img, box, 16)
    d = ImageDraw.Draw(img)
    rr(d, box, 16, fill=(250, 250, 248, 255), outline=(222, 226, 223, 255), width=2)
    rr(d, (x0 + 8, 300, x0 + w - 8, 500), 10, fill=(238, 241, 238, 255))
    for p in range(poles):
        px = x0 + 20 + p * 110 + (20 if rcd else 0)
        rr(d, (px + 22, 330, px + 68, 430), 10, fill=(40, 120, 70, 255) if not rcd else (35, 90, 170, 255))
        rr(d, (px + 26, 334, px + 64, 380), 8, fill=(255, 255, 255, 70))
        for sy in (205, 545):
            ell(d, (px + 25, sy + 10, px + 65, sy + 50), fill=(200, 205, 202, 255))
            d.line([((px + 30) * S, (sy + 30) * S), ((px + 60) * S, (sy + 30) * S)], fill=(120, 126, 122, 255), width=4 * S)
    if rcd:
        rr(d, (x0 + 14, 450, x0 + 44, 480), 6, fill=(240, 180, 40, 255))
    rr(d, (x0 + 14, 515, x0 + w - 14, 530), 4, fill=ACCENT + (255,))
    return img


def draw_board():
    img = backdrop()
    box = (180, 150, 620, 650)
    shadow(img, box, 24)
    d = ImageDraw.Draw(img)
    rr(d, box, 24, fill=(245, 246, 244, 255), outline=(220, 224, 221, 255), width=3)
    rr(d, (215, 190, 585, 610), 16, fill=(230, 234, 231, 255))
    rr(d, (240, 300, 560, 470), 12, fill=(160, 205, 230, 120), outline=(150, 160, 156, 255), width=3)
    for i in range(6):
        x = 258 + i * 50
        rr(d, (x, 320, x + 40, 450), 6, fill=(252, 252, 250, 255))
        rr(d, (x + 10, 360, x + 30, 400), 4, fill=(40, 120, 70, 255))
    rr(d, (370, 520, 430, 545), 8, fill=(150, 156, 152, 255))
    return img


def draw_strip():
    img = backdrop()
    shadow(img, (140, 330, 660, 470), 40)
    d = ImageDraw.Draw(img)
    rr(d, (140, 330, 660, 470), 40, fill=(250, 250, 248, 255), outline=(222, 226, 223, 255), width=2)
    for i in range(4):
        cx = 240 + i * 100
        ell(d, (cx - 38, 362, cx + 38, 438), fill=(236, 239, 236, 255))
        ell(d, (cx - 22, 392, cx - 8, 406), fill=(60, 66, 63, 255))
        ell(d, (cx + 8, 392, cx + 22, 406), fill=(60, 66, 63, 255))
    rr(d, (590, 375, 640, 425), 12, fill=(220, 50, 50, 255))
    d.line([(150 * S, 400 * S), (90 * S, 400 * S), (90 * S, 600 * S), (300 * S, 640 * S)], fill=(250, 250, 248, 255), width=16 * S, joint='curve')
    return img


def draw_contactor():
    img = backdrop()
    box = (250, 190, 550, 610)
    shadow(img, box, 18)
    d = ImageDraw.Draw(img)
    rr(d, box, 18, fill=(70, 76, 73, 255))
    rr(d, (270, 210, 530, 300), 10, fill=(90, 96, 93, 255))
    rr(d, (270, 500, 530, 590), 10, fill=(90, 96, 93, 255))
    for x in (300, 400, 500):
        ell(d, (x - 22, 233, x + 22, 277), fill=(190, 160, 70, 255))
        ell(d, (x - 22, 523, x + 22, 567), fill=(190, 160, 70, 255))
    rr(d, (300, 330, 500, 470), 14, fill=(240, 242, 240, 255))
    rr(d, (360, 360, 440, 440), 10, fill=ACCENT + (255,))
    return img


def draw_meter():
    img = backdrop()
    box = (260, 160, 540, 640)
    shadow(img, box, 40)
    d = ImageDraw.Draw(img)
    rr(d, box, 40, fill=(245, 190, 30, 255))
    rr(d, (280, 180, 520, 620), 30, fill=(45, 50, 48, 255))
    rr(d, (305, 210, 495, 320), 12, fill=(190, 215, 190, 255))
    t = font(56, 700, latin=True)
    d.text((400 * S, 268 * S), '230.4', font=t, fill=(30, 40, 35, 255), anchor='mm')
    ell(d, (330, 370, 470, 510), fill=(80, 86, 83, 255))
    ell(d, (370, 410, 430, 470), fill=(60, 64, 62, 255))
    d.line([(400 * S, 440 * S), (440 * S, 395 * S)], fill=(245, 190, 30, 255), width=8 * S)
    for x, c in ((330, (40, 40, 40)), (400, (220, 40, 40)), (470, (40, 40, 40))):
        ell(d, (x - 18, 560, x + 18, 596), fill=c + (255,))
    return img


def draw_tester():
    img = backdrop()
    shadow(img, (380, 140, 420, 660), 20, blur=18, offset=12)
    layer = Image.new('RGBA', img.size, (0, 0, 0, 0))
    d = ImageDraw.Draw(layer)
    rr(d, (360, 130, 440, 430), 34, fill=(220, 40, 40, 255))
    rr(d, (372, 160, 428, 300), 20, fill=(255, 210, 120, 160))
    rr(d, (380, 430, 420, 470), 8, fill=(60, 66, 63, 255))
    rr(d, (390, 470, 410, 660), 6, fill=(200, 205, 202, 255))
    d.polygon([(390 * S, 650 * S), (410 * S, 650 * S), (400 * S, 690 * S)], fill=(200, 205, 202, 255))
    layer = layer.rotate(-28, center=(400 * S, 410 * S), resample=Image.BICUBIC)
    img.alpha_composite(layer)
    return img


def draw_flood():
    img = backdrop()
    shadow(img, (190, 210, 610, 560), 28)
    d = ImageDraw.Draw(img)
    rr(d, (190, 210, 610, 560), 28, fill=(55, 60, 58, 255))
    for x in range(215, 600, 26):
        rr(d, (x, 222, x + 12, 268), 4, fill=(75, 80, 78, 255))
    rr(d, (225, 290, 575, 530), 16, fill=(235, 238, 236, 255))
    g = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(g).rounded_rectangle([v * S for v in (250, 310, 550, 510)], 20 * S, fill=(255, 255, 215, 220))
    img.alpha_composite(g.filter(ImageFilter.GaussianBlur(16 * S)))
    d = ImageDraw.Draw(img)
    rr(d, (370, 560, 430, 640), 10, fill=(55, 60, 58, 255))
    return img


def draw_wall_lamp():
    img = backdrop()
    shadow(img, (300, 200, 500, 600), 30)
    d = ImageDraw.Draw(img)
    rr(d, (300, 200, 500, 600), 30, fill=(48, 52, 50, 255))
    rr(d, (325, 230, 475, 570), 20, fill=(70, 74, 72, 255))
    g = Image.new('RGBA', img.size, (0, 0, 0, 0))
    gd = ImageDraw.Draw(g)
    gd.polygon([(340 * S, 260 * S), (460 * S, 260 * S), (520 * S, 120 * S), (280 * S, 120 * S)], fill=(255, 245, 190, 120))
    gd.polygon([(340 * S, 540 * S), (460 * S, 540 * S), (520 * S, 690 * S), (280 * S, 690 * S)], fill=(255, 245, 190, 120))
    img.alpha_composite(g.filter(ImageFilter.GaussianBlur(18 * S)))
    return img


def draw_box():
    img = backdrop()
    shadow(img, (230, 260, 570, 600), 20)
    d = ImageDraw.Draw(img)
    d.polygon([(230 * S, 330 * S), (400 * S, 260 * S), (570 * S, 330 * S), (400 * S, 400 * S)], fill=(232, 236, 233, 255))
    d.polygon([(230 * S, 330 * S), (400 * S, 400 * S), (400 * S, 600 * S), (230 * S, 530 * S)], fill=(210, 216, 212, 255))
    d.polygon([(570 * S, 330 * S), (400 * S, 400 * S), (400 * S, 600 * S), (570 * S, 530 * S)], fill=(196, 202, 198, 255))
    for cx, cy in ((290, 440), (340, 460), (460, 460), (510, 440)):
        ell(d, (cx - 16, cy - 16, cx + 16, cy + 16), fill=(170, 176, 172, 255))
    return img


PRODUCTS = {
    'switch-white': lambda: draw_switch((250, 250, 248)),
    'switch-black': lambda: draw_switch((44, 48, 46)),
    'switch-cream': lambda: draw_switch((240, 230, 205)),
    'switch-gray': lambda: draw_switch((150, 156, 153)),
    'switch-double': lambda: draw_switch((250, 250, 248), 2),
    'switch-double-black': lambda: draw_switch((44, 48, 46), 2),
    'socket': lambda: draw_socket(),
    'socket-black': lambda: draw_socket((44, 48, 46)),
    'socket-usb': lambda: draw_socket(usb=True),
    'bulb': lambda: draw_bulb(),
    'panel-square': lambda: draw_panel(),
    'panel-round': lambda: draw_panel(True),
    'spot': draw_spot,
    'cable-red': lambda: draw_cable((210, 40, 40)),
    'cable-blue': lambda: draw_cable((30, 90, 200)),
    'cable-yellow': lambda: draw_cable((230, 190, 30)),
    'spool': lambda: draw_spool(),
    'breaker-1p': lambda: draw_breaker(1),
    'breaker-3p': lambda: draw_breaker(3),
    'rcd': lambda: draw_breaker(2, True),
    'board': draw_board,
    'strip': draw_strip,
    'contactor': draw_contactor,
    'meter': draw_meter,
    'tester': draw_tester,
    'flood': draw_flood,
    'wall-lamp': draw_wall_lamp,
    'box': draw_box,
}


# ---------------------------------------------------------------- category tiles (transparent PNG)

def category_tile(product_key, path):
    src = PRODUCTS[product_key]()
    # Cut the product off its backdrop by chroma: keep the central area, fade edges.
    img = src.resize((400 * S, 400 * S), Image.LANCZOS)
    mask = Image.new('L', img.size, 0)
    ImageDraw.Draw(mask).ellipse((20 * S, 20 * S, 380 * S, 380 * S), fill=255)
    mask = mask.filter(ImageFilter.GaussianBlur(16 * S))
    tile = Image.new('RGBA', img.size, (0, 0, 0, 0))
    tile.paste(img, (0, 0), mask)
    tile.resize((400, 400), Image.LANCZOS).save(path, 'PNG', optimize=True)


# ---------------------------------------------------------------- brand logos

def brand_logo(name, mark_color, path):
    img = canvas(400, 200)
    d = ImageDraw.Draw(img)
    rr(d, (40, 60, 120, 140), 22, fill=mark_color + (255,))
    d.polygon([(86 * S, 72 * S), (62 * S, 104 * S), (80 * S, 104 * S), (72 * S, 128 * S), (100 * S, 92 * S), (82 * S, 92 * S)], fill=(255, 255, 255, 255))
    d.text((140 * S, 100 * S), name, font=font(46, 900, latin=True), fill=TEXT + (255,), anchor='lm')
    finish(img, path, 400, 200, quality=90)


# ---------------------------------------------------------------- banners / posts

def scene(w, h, keys, title=None, sub=None, dark=True, path=None):
    top, bottom = ((6, 78, 59), (4, 52, 40)) if dark else ((236, 243, 238), (220, 232, 224))
    img = gradient(w, h, top, bottom)
    glow = Image.new('RGBA', img.size, (0, 0, 0, 0))
    gd = ImageDraw.Draw(glow)
    gd.ellipse((-w * S * 0.1, -h * S * 0.4, w * S * 0.5, h * S * 0.7), fill=ACCENT + (60 if dark else 90,))
    gd.ellipse((w * S * 0.55, h * S * 0.3, w * S * 1.2, h * S * 1.4), fill=(255, 255, 255, 30))
    img.alpha_composite(glow.filter(ImageFilter.GaussianBlur(80 * S)))

    # Product cards on the right; the hero widget puts its text on the left (align "end" in RTL).
    n = len(keys)
    hero = w / h > 2
    size = int(h * (0.46 if hero else 0.52))
    gap = int(size * 0.12)
    total = n * size + (n - 1) * gap
    x = int(w * 0.95) - total if hero else int(w * 0.5 - total / 2)
    y = int(h * 0.5 - size / 2)
    for i, key in enumerate(keys):
        prod = PRODUCTS[key]().resize((size * S, size * S), Image.LANCZOS)
        mask = Image.new('L', prod.size, 0)
        ImageDraw.Draw(mask).rounded_rectangle((0, 0, size * S, size * S), 40 * S, fill=255)
        yy = y + (int(size * 0.08) if i % 2 else -int(size * 0.08))
        shadow(img, (x, yy, x + size, yy + size), 40, blur=30, offset=20, alpha=70)
        img.paste(prod, (x * S, yy * S), mask)
        x += size + gap
    if title:
        d = ImageDraw.Draw(img)
        d.text((w * S * 0.94, h * S * 0.40), title, font=font(int(h * 0.075), 900), fill=(255, 255, 255, 255) if dark else TEXT + (255,), anchor='rm', direction='rtl')
        if sub:
            d.text((w * S * 0.94, h * S * 0.53), sub, font=font(int(h * 0.04), 500), fill=(255, 255, 255, 210) if dark else TEXT + (200,), anchor='rm', direction='rtl')
    finish(img, path, w, h)


def main(out):
    os.makedirs(out, exist_ok=True)
    for key, fn in PRODUCTS.items():
        finish(fn(), os.path.join(out, 'product-%s.jpg' % key), 800, 800)

    tiles = {
        'cat-switches': 'switch-white', 'cat-lighting': 'panel-square', 'cat-cables': 'cable-red',
        'cat-industrial': 'contactor', 'cat-protection': 'breaker-3p', 'cat-lamps': 'bulb', 'cat-tools': 'meter',
    }
    for name, key in tiles.items():
        category_tile(key, os.path.join(out, '%s.png' % name))

    brands = {'arka': ('ARKA', PRIMARY), 'noran': ('NORAN', (230, 150, 20)), 'voltis': ('VOLTIS', (30, 90, 200)),
              'simatek': ('SIMATEK', (210, 40, 40)), 'partow': ('PARTOW', (110, 60, 180))}
    for slug, (name, color) in brands.items():
        brand_logo(name, color, os.path.join(out, 'brand-%s.jpg' % slug))

    # Hero images carry no text: the hero widget renders its own (editable) heading over them.
    scene(1600, 700, ['switch-white', 'breaker-3p'], dark=False, path=os.path.join(out, 'hero-1.jpg'))
    scene(1600, 700, ['cable-blue', 'board'], dark=False, path=os.path.join(out, 'hero-2.jpg'))
    scene(1600, 700, ['bulb', 'flood'], dark=False, path=os.path.join(out, 'hero-3.jpg'))

    posts = [
        ('post-1', ['switch-white', 'socket']), ('post-2', ['cable-red', 'spool']), ('post-3', ['panel-square', 'bulb']),
        ('post-4', ['breaker-1p', 'rcd']), ('post-5', ['meter', 'tester']), ('post-6', ['board', 'contactor']),
    ]
    for i, (name, keys) in enumerate(posts):
        scene(1200, 675, keys, dark=bool(i % 2 == 0), path=os.path.join(out, '%s.jpg' % name))

    scene(1200, 800, ['switch-double', 'socket-usb', 'strip'], dark=True, path=os.path.join(out, 'about.jpg'))


if __name__ == '__main__':
    main(sys.argv[1] if len(sys.argv) > 1 else os.path.join(ROOT, 'samadi-core/demo/images'))
