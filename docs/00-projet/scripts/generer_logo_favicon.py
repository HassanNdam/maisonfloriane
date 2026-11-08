"""Déclinaisons web du logo Floréa Skin et jeu de favicons.
Usage : python 00-projet/scripts/generer_logo_favicon.py  (depuis la racine du projet)"""
import json, os
import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageOps

SRC = "99-sources-originales/logos/logo-florea-skin-original.jpeg"
LOGO = "01-images-site/logos/florea-skin"
FAV = "01-images-site/favicon"
CREME = (247, 238, 224)
os.makedirs(LOGO, exist_ok=True)
os.makedirs(FAV, exist_ok=True)

im = ImageOps.exif_transpose(Image.open(SRC)).convert("RGB")
a = np.asarray(im).astype(int)
r, b = a[..., 0], a[..., 2]

# Cercle doré extérieur = boîte englobante des pixels dorés
ys, xs = np.nonzero((r - b > 70) & (r < 235))
cx, cy = (xs.min() + xs.max()) / 2, (ys.min() + ys.max()) / 2
R = max(xs.max() - xs.min(), ys.max() - ys.min()) / 2 + 3


def rond_transparent():
    """Logo découpé au ras de l'anneau doré, extérieur transparent (masque lissé x4)."""
    k = 4
    m = Image.new("L", (im.width * k, im.height * k), 0)
    ImageDraw.Draw(m).ellipse([(cx - R) * k, (cy - R) * k, (cx + R) * k, (cy + R) * k], fill=255)
    out = im.convert("RGBA")
    out.putalpha(m.resize(im.size, Image.LANCZOS))
    return out.crop((int(cx - R), int(cy - R), int(cx + R), int(cy + R)))


rond = rond_transparent()

# --- Logos ---
carre = im.copy()
carre.thumbnail((1200, 1200), Image.LANCZOS)
carre.save(f"{LOGO}/logo-florea-skin-la-beaute-revelee-par-la-nature.webp", "WEBP", quality=88, method=6)
rond_1200 = rond.resize((1200, 1200), Image.LANCZOS)
rond_1200.save(f"{LOGO}/logo-florea-skin-rond-fond-transparent.png", optimize=True)
rond_1200.save(f"{LOGO}/logo-florea-skin-rond-fond-transparent.webp", "WEBP", quality=88, method=6)
rond.resize((400, 400), Image.LANCZOS).save(f"{LOGO}/logo-florea-skin-rond-400px.webp", "WEBP", quality=90, method=6)

# --- Monogramme « F » doré sur fond transparent (petites tailles) ---
# Zone du F de FLORÉA, repérée sur l'original 1284 px
f = a[565:758, 167:327]
# le fond crème a un écart rouge-bleu ≈ 20-40 : seuil à 45 pour éviter un voile autour de la lettre.
# Les reflets clairs du métal passent sous le seuil : on referme la forme puis on la remplit
# d'un doré uni, plus lisible en 16 px que le dégradé métallique.
mask = Image.fromarray(np.where(f[..., 0] - f[..., 2] > 45, 255, 0).astype(np.uint8))
mask = mask.filter(ImageFilter.MaxFilter(7)).filter(ImageFilter.MinFilter(7)).filter(ImageFilter.GaussianBlur(0.8))
F = Image.new("RGBA", mask.size, (176, 128, 40, 255))
F.putalpha(mask)
F = F.crop(F.getbbox())


def monogramme(size, pad=0.08):
    c = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    t = F.copy()
    if size <= 48:  # les empattements font moins d'un pixel en 16 px : on épaissit le trait avant réduction
        t.putalpha(t.getchannel("A").filter(ImageFilter.MaxFilter(11)))
    t.thumbnail((int(size * (1 - 2 * pad)),) * 2, Image.LANCZOS)
    c.alpha_composite(t, ((size - t.width) // 2, (size - t.height) // 2))
    return c


def icone_ronde(size, fond=True, marge=0.0):
    """Logo rond sur carré crème opaque (iOS/Android refusent la transparence)."""
    c = Image.new("RGBA", (size, size), CREME + (255,) if fond else (0, 0, 0, 0))
    d = int(size * (1 - 2 * marge))
    c.alpha_composite(rond.resize((d, d), Image.LANCZOS), ((size - d) // 2,) * 2)
    return c


monogramme(16).save(f"{FAV}/favicon-16x16.png", optimize=True)
monogramme(32).save(f"{FAV}/favicon-32x32.png", optimize=True)
monogramme(48).save(f"{FAV}/favicon.ico", sizes=[(16, 16), (32, 32), (48, 48)],
                    append_images=[monogramme(16), monogramme(32)])
icone_ronde(180, marge=0.03).convert("RGB").save(f"{FAV}/apple-touch-icon.png", optimize=True)
icone_ronde(192, marge=0.03).save(f"{FAV}/android-chrome-192x192.png", optimize=True)
icone_ronde(512, marge=0.03).save(f"{FAV}/android-chrome-512x512.png", optimize=True)
# Android « maskable » : le système rogne en cercle/goutte, le logo doit tenir dans les 80 % centraux
icone_ronde(512, marge=0.11).save(f"{FAV}/maskable-icon-512x512.png", optimize=True)

with open(f"{FAV}/site.webmanifest", "w", encoding="utf-8") as fh:
    json.dump({
        "name": "Maison Floriane", "short_name": "Maison Floriane", "lang": "fr",
        "icons": [
            {"src": "/android-chrome-192x192.png", "sizes": "192x192", "type": "image/png"},
            {"src": "/android-chrome-512x512.png", "sizes": "512x512", "type": "image/png"},
            {"src": "/maskable-icon-512x512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable"},
        ],
        "theme_color": "#F7EEE0", "background_color": "#F7EEE0", "display": "standalone", "start_url": "/",
    }, fh, ensure_ascii=False, indent=2)

with open(f"{FAV}/a-copier-dans-le-head.html", "w", encoding="utf-8") as fh:
    fh.write("""<!-- Favicons : déposer les fichiers de ce dossier à la racine du site -->
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#F7EEE0">
""")
print("centre", cx, cy, "rayon", R)
