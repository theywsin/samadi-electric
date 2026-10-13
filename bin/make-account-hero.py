#!/usr/bin/env python3
"""Generate the default welcome-banner image for Samadi Account Dashboard.

Original illustration: warm wall, green slatted panel with a plant, stone pedestal with
three switch plates on the left; the right side stays calm for the RTL greeting text.

    python3 bin/make-account-hero.py samadi-account/assets/images/hero.jpg
"""
import importlib.util
import os
import random
import sys

from PIL import Image, ImageDraw, ImageFilter

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
spec = importlib.util.spec_from_file_location('demo', os.path.join(ROOT, 'bin/make-demo-images.py'))
demo = importlib.util.module_from_spec(spec)
spec.loader.exec_module(demo)

W, H, S = 1600, 420, 2


def plate_sprite(kind, size):
    """A switch plate cut out of its backdrop (transparent PNG)."""
    img = {'double-black': lambda: demo.draw_switch((44, 48, 46), 2),
           'socket-black': lambda: demo.draw_socket((44, 48, 46)),
           'single-white': lambda: demo.draw_switch((250, 250, 248))}[kind]()
    img = img.crop((190 * S, 190 * S, 610 * S, 610 * S))
    mask = Image.new('L', img.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((10 * S, 10 * S, 410 * S, 410 * S), 56 * S, fill=255)
    out = Image.new('RGBA', img.size, (0, 0, 0, 0))
    out.paste(img, (0, 0), mask)
    return out.resize((size * S, size * S), Image.LANCZOS)


def main(path):
    random.seed(7)
    img = demo.gradient(W, H, (240, 230, 216), (226, 212, 194))
    d = ImageDraw.Draw(img)

    # Leaf shadows across the wall.
    shade = Image.new('RGBA', img.size, (0, 0, 0, 0))
    sd = ImageDraw.Draw(shade)
    for _ in range(26):
        x = random.randint(280, 1100) * S
        y = random.randint(-40, 220) * S
        w = random.randint(30, 70) * S
        sd.ellipse((x, y, x + w, y + w * 2.6), fill=(90, 70, 50, 34))
    img.alpha_composite(shade.filter(ImageFilter.GaussianBlur(18 * S)))

    # Green slatted panel on the far left.
    d.rectangle((0, 0, 150 * S, H * S), fill=(18, 64, 46, 255))
    for x in range(8, 150, 22):
        d.rectangle((x * S, 0, (x + 12) * S, H * S), fill=(28, 84, 60, 255))
    edge = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(edge).rectangle((140 * S, 0, 170 * S, H * S), fill=(0, 0, 0, 60))
    img.alpha_composite(edge.filter(ImageFilter.GaussianBlur(10 * S)))

    # Plant in a pot.
    d = ImageDraw.Draw(img)
    for i in range(14):
        ang = -60 + i * 9
        x0, y0 = 120, 300
        import math
        x1 = x0 + math.cos(math.radians(ang - 90)) * 160
        y1 = y0 + math.sin(math.radians(ang - 90)) * 170
        col = (40 + i * 4, 110 + (i % 3) * 14, 60, 255)
        d.line([(x0 * S, y0 * S), (x1 * S, y1 * S)], fill=col, width=5 * S)
        d.ellipse(((x1 - 18) * S, (y1 - 7) * S, (x1 + 18) * S, (y1 + 7) * S), fill=col)
    d.rounded_rectangle((78 * S, 292 * S, 162 * S, 380 * S), 14 * S, fill=(196, 160, 120, 255))
    d.rectangle((78 * S, 292 * S, 162 * S, 306 * S), fill=(176, 140, 104, 255))

    # Stone pedestal.
    demo.shadow(img, (230, 300, 760, 380), 16, blur=22, offset=14, alpha=70)
    slab = Image.new('RGBA', img.size, (0, 0, 0, 0))
    sd = ImageDraw.Draw(slab)
    sd.rounded_rectangle((230 * S, 290 * S, 760 * S, 380 * S), 14 * S, fill=(214, 204, 190, 255))
    sd.rounded_rectangle((230 * S, 290 * S, 760 * S, 312 * S), 10 * S, fill=(232, 224, 212, 255))
    for _ in range(1600):
        x = random.randint(232, 758) * S
        y = random.randint(292, 378) * S
        c = random.choice([(190, 178, 162), (226, 218, 206), (170, 156, 140), (205, 193, 178)])
        sd.point((x, y), fill=c + (255,))
    img.alpha_composite(slab.filter(ImageFilter.GaussianBlur(0.6 * S)))

    # Switch plates standing on the pedestal.
    for kind, x in (('double-black', 270), ('socket-black', 430), ('single-white', 590)):
        size = 140
        y = 300 - size
        demo.shadow(img, (x + 8, y + 10, x + size - 8, y + size), 26, blur=14, offset=8, alpha=80)
        img.alpha_composite(plate_sprite(kind, size), (x * S, y * S))

    # Calm the right side for the text.
    calm = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(calm).rectangle((W * S * 0.55, 0, W * S, H * S), fill=(242, 234, 222, 150))
    img.alpha_composite(calm.filter(ImageFilter.GaussianBlur(80 * S)))

    demo.finish(img, path, W, H, quality=84)


if __name__ == '__main__':
    main(sys.argv[1] if len(sys.argv) > 1 else os.path.join(ROOT, 'samadi-account/assets/images/hero.jpg'))
