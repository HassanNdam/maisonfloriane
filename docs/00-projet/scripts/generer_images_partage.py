"""Images de partage (Open Graph) 1200 x 630 pour Facebook, WhatsApp, Pinterest, LinkedIn…

À partir des photos du site (01-images-site/florea-skin), écrit des JPG dans 01-images-site/partage/.
Usage : python generer_images_partage.py [dossier_racine]   (racine = dossier OneDrive « Maison floriane »)
Les JPG sont ensuite copiés dans docs/01-images-site/partage/ du dépôt ; le seed les importe (scripts/seo.php).
"""
import sys
from pathlib import Path

from PIL import Image, ImageFilter

RACINE = Path(sys.argv[1]) if len(sys.argv) > 1 else Path(__file__).resolve().parents[2]
SOURCE = RACINE / "01-images-site" / "florea-skin"
SORTIE = RACINE / "01-images-site" / "partage"
L, H = 1200, 630

# image de sortie -> (photo source, centre vertical du cadrage en fraction de la hauteur, mode)
IMAGES = {
    "partage-florea-skin-savon-noir-glow-oil-rituel.jpg": ("duo-savon-noir-glow-oil-florea-skin-rituel.webp", 0.62, "cadre"),
    "partage-savon-noir-reparateur-florea-skin.jpg": ("savon-noir-reparateur-florea-skin-en-main.webp", 0.46, "cadre"),
    "partage-glow-oil-florea-skin-huile-eclat.jpg": ("glow-oil-florea-skin-huile-corporelle-en-main.webp", 0.40, "flou"),
    "partage-flore-fondatrice-florea-skin.jpg": ("flore-fondatrice-florea-skin-portrait.webp", 0.35, "flou"),
}


def trouver(nom: str) -> Path:
    trouves = list(SOURCE.rglob(nom)) or list((RACINE / "01-images-site").rglob(nom))
    if not trouves:
        raise SystemExit(f"Photo introuvable : {nom}")
    return trouves[0]


def cadre(img: Image.Image, centre: float) -> Image.Image:
    """Remplit 1200 x 630 en recadrant (paysage)."""
    echelle = max(L / img.width, H / img.height)
    img = img.resize((round(img.width * echelle), round(img.height * echelle)), Image.LANCZOS)
    x = (img.width - L) // 2
    y = min(max(round(img.height * centre - H / 2), 0), img.height - H)
    return img.crop((x, y, x + L, y + H))


def flou(img: Image.Image, centre: float) -> Image.Image:
    """Photo verticale entière au centre, sur un fond flou de la même photo."""
    fond = cadre(img, centre).filter(ImageFilter.GaussianBlur(28))
    fond = Image.blend(fond, Image.new("RGB", fond.size, (244, 238, 228)), 0.35)
    echelle = H / img.height
    avant = img.resize((round(img.width * echelle), H), Image.LANCZOS)
    fond.paste(avant, ((L - avant.width) // 2, 0))
    return fond


SORTIE.mkdir(parents=True, exist_ok=True)
for nom, (source, centre, mode) in IMAGES.items():
    img = Image.open(trouver(source)).convert("RGB")
    resultat = cadre(img, centre) if mode == "cadre" else flou(img, centre)
    resultat.save(SORTIE / nom, "JPEG", quality=85, optimize=True, progressive=True)
    print(f"{nom} ({(SORTIE / nom).stat().st_size // 1024} Ko)")
