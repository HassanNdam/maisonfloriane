"""Détourage géométrique du flacon de Glow Oil (photo du duo, IMG-20261109-WA0004.jpg).
Les flancs du flacon sont droits : on mesure les bords (gradient) dans des fenêtres étroites, on ajuste une droite,
puis on dessine le masque (bouchon + épaule + corps aux coins arrondis) en 4x et on le réduit (anticrénelage)."""
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

SP = r'C:\Users\hndam\AppData\Local\Temp\claude\c1896ca86ffa3017\e706889c-6256-4de0-9545-5e5d0be3444b\scratchpad'
SRC = r'C:\Users\hndam\OneDrive\Bureau\Projets_Dev\Maison floriane\99-sources-originales\images\produits\IMG-20261109-WA0004.jpg'
X0, Y0 = 700, 240
img = Image.open(SRC).convert('RGB').crop((X0, Y0, 960, 800))
a = np.asarray(img).astype(float)
gris = a.mean(axis=2)
gx = np.abs(np.diff(gris, axis=1))
gy = np.abs(np.diff(gris, axis=0))


def bord(y, x1, x2):
    return x1 + int(np.argmax(gx[y, x1:x2]))


# Bords du corps (lignes 175 à 495), fenêtres autour des positions attendues
ys = np.arange(175, 496, 5)
gauches = np.array([bord(y, 45, 80) for y in ys])
droites = np.array([bord(y, 190, 222) + 1 for y in ys])
pg = np.polyfit(ys, gauches, 1)
pd = np.polyfit(ys, droites, 1)
print('gauche x = %.3f*y + %.1f' % tuple(pg), ' droite x = %.3f*y + %.1f' % tuple(pd))
# Bas du flacon : plus fort gradient vertical au centre
cx = slice(90, 170)
bas = 480 + int(np.argmax(gy[480:545, cx].mean(axis=1)))
# Bouchon : bords gauche/droit et haut
cg = int(np.median([bord(y, 70, 100) for y in range(60, 140, 4)]))
cd = int(np.median([bord(y, 175, 200) + 1 for y in range(60, 140, 4)]))
haut = 20 + int(np.argmax(gy[20:70, 100:175].mean(axis=1)))
bas_bouchon = 140 + int(np.argmax(gy[140:165, 100:175].mean(axis=1)))
print('bouchon', cg, cd, haut, bas_bouchon, ' bas du flacon', bas)

S = 4
masque = Image.new('L', (img.width * S, img.height * S), 0)
d = ImageDraw.Draw(masque)
xg = lambda y: np.polyval(pg, y)
xd = lambda y: np.polyval(pd, y)
epaule = bas_bouchon + 14  # fin de l'arrondi de l'épaule
r_bas = 10
pts = []
# Bouchon (coins supérieurs légèrement arrondis)
haut += 2  # le premier rang de pixels du bouchon est encore mêlé au fond
pts += [(cg + 2, haut), (cd - 2, haut), (cd, haut + 3), (cd, bas_bouchon)]
# Épaule droite (quart d'ellipse du bouchon vers le corps)
for t in np.linspace(0, np.pi / 2, 12):
    pts.append((cd + (xd(epaule) - cd) * np.sin(t), bas_bouchon + (epaule - bas_bouchon) * (1 - np.cos(t))))
# Flanc droit, puis pied du flacon : demi-ellipse (vue légèrement plongeante, le centre du pied est plus bas que les côtés)
ry = 13
fond_y = bas - 3
cote_y = fond_y - ry
pts.append((xd(cote_y), cote_y))
xc = (xg(cote_y) + xd(cote_y)) / 2
rx = (xd(cote_y) - xg(cote_y)) / 2
for t in np.linspace(0, np.pi, 40):
    pts.append((xc + rx * np.cos(t), cote_y + ry * np.sin(t)))
pts.append((xg(epaule), epaule))
# Épaule gauche
for t in np.linspace(np.pi / 2, 0, 12):
    pts.append((cg - (cg - xg(epaule)) * np.sin(t), bas_bouchon + (epaule - bas_bouchon) * (1 - np.cos(t))))
pts += [(cg, haut + 3)]
d.polygon([(x * S, y * S) for x, y in pts], fill=255)
masque = masque.resize(img.size, Image.LANCZOS).filter(ImageFilter.GaussianBlur(0.6))

rgba = img.copy()
rgba.putalpha(masque)
boite = masque.getbbox()
rgba = rgba.crop((boite[0] - 6, boite[1] - 6, boite[2] + 6, boite[3] + 6))
rgba.save(SP + r'\glow-oil-detoure.png')
print('taille', rgba.size)

# Aperçu sur le fond de la charte et sur un damier
fond = Image.new('RGBA', (rgba.width * 3 + 40, rgba.height + 20), (239, 230, 217, 255))
fond.alpha_composite(rgba, (10, 10))
damier = Image.new('RGBA', rgba.size)
dd = ImageDraw.Draw(damier)
for yy in range(0, rgba.height, 12):
    for xx in range(0, rgba.width, 12):
        dd.rectangle([xx, yy, xx + 11, yy + 11], fill=(200, 200, 200, 255) if (xx + yy) // 12 % 2 else (255, 255, 255, 255))
damier.alpha_composite(rgba)
fond.alpha_composite(damier, (rgba.width + 20, 10))
noir = Image.new('RGBA', rgba.size, (46, 42, 38, 255))
noir.alpha_composite(rgba)
fond.alpha_composite(noir, (rgba.width * 2 + 30, 10))
fond.convert('RGB').resize((fond.width * 2, fond.height * 2), Image.LANCZOS).save(SP + r'\glow-detoure-apercu.jpg', quality=88)
