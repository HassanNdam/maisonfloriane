"""Génère catalogue-produits.xlsx (édition par la propriétaire) et catalogue-produits.json (import du site).
Périmètre au 08/11/2026 : 2 produits Floréa Skin, vente en Europe, prix non fixés.
Usage : python 00-projet/scripts/generer_catalogue.py  (depuis la racine du projet)"""
import json, os, re, unicodedata
from openpyxl import Workbook
from openpyxl.styles import Alignment, Font, PatternFill, Border, Side
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.formatting.rule import FormulaRule

OUT = "00-projet"
IMG = "01-images-site/florea-skin/"
VID = "02-videos-site/florea-skin/"
SEUIL_STOCK_LIMITE = 3
CONTACT = {"email": "florekhadije@gmail.com", "telephone": "+41 79 273 83 53", "whatsapp": "+41 79 273 83 53"}
TEST_PEAU = "Faire un test sur une petite zone avant la première utilisation."


def slug(s):
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode()
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


P = [
    dict(ref="FS-001", nom="Savon noir réparateur Floréa Skin", categorie="Nettoyant visage et corps", contenance="300 g", prix=45.99, ingredients="Extraits de plantes, curcuma, miel, neem, glycérine",
         image=IMG + "produits/savon-noir-reparateur-florea-skin-detoure.webp",
         video=VID + "produits/video-savon-noir-florea-skin-presentation.mp4",
         desc_courte="Savon noir réparateur pour le visage et le corps : nettoie en profondeur et prépare la peau.",
         desc="Le savon noir réparateur Floréa Skin nettoie le visage et le corps en douceur tout en respectant la peau. "
              "Il élimine les impuretés et laisse la peau nette, prête à recevoir le Glow Oil. "
              "Floréa Skin, la beauté révélée par la nature.",
         conseils="Faire mousser une petite quantité avec de l'eau, masser le visage et le corps, puis rincer abondamment. "
                  "Éviter le contour des yeux. Usage quotidien. " + TEST_PEAU),
    dict(ref="FS-002", nom="Glow Oil Floréa Skin", categorie="Huile corporelle", contenance="100 ml", prix=35.99, ingredients="Huile de busserole, huile de jojoba, vitamine C, glutathion, glycérine",
         image=IMG + "produits/glow-oil-florea-skin-huile-corporelle-en-main.webp",
         video=VID + "produits/video-glow-oil-florea-skin-presentation.mp4",
         desc_courte="Huile corporelle éclatante, nourrissante et réparatrice : le booster d'éclat de votre routine.",
         desc="Le Glow Oil Floréa Skin est une huile corporelle éclatante, nourrissante et réparatrice. "
              "Il s'utilise seul ou en booster, mélangé à votre crème corporelle, pour une peau souple et lumineuse. "
              "Floréa Skin, la beauté révélée par la nature.",
         conseils="Appliquer quelques gouttes sur une peau propre et légèrement humide, masser jusqu'à pénétration. "
                  "Peut se mélanger à votre crème corporelle pour booster l'éclat. " + TEST_PEAU),
    # Duo : produit à part ; prix normal = somme des deux, prix promo = prix du duo (WooCommerce barre l'ancien prix)
    dict(ref="FS-003", nom="Le Duo rituel Floréa Skin", categorie="Coffrets et routines", contenance="Savon noir 300 g + Glow Oil 100 ml",
         prix=81.98, prix_promo=69.99,
         ingredients="Savon noir : extraits de plantes, curcuma, miel, neem, glycérine. Glow Oil : huile de busserole, huile de jojoba, vitamine C, glutathion, glycérine",
         image=IMG + "produits/duo-savon-noir-glow-oil-florea-skin-rituel.webp", video=None,
         desc_courte="Le savon noir réparateur et le Glow Oil réunis : la routine complète de Flore, à prix doux.",
         desc="Le Duo rituel réunit les deux gestes Floréa Skin : le savon noir réparateur pour purifier, puis le Glow Oil pour nourrir et révéler l'éclat. "
              "Floréa Skin, la beauté révélée par la nature.",
         conseils="Matin et soir : nettoyer avec le savon noir, rincer, puis appliquer quelques gouttes de Glow Oil sur une peau légèrement humide. " + TEST_PEAU),
]
for p in P:
    p.update(marque="Floréa Skin", genre="Mixte", slug=slug(p["nom"]), alt=p["nom"])
    titre = p["nom"] + " | Maison Floriane"
    p["meta_title"] = titre if len(titre) <= 60 else p["nom"]
    p["meta_description"] = (p["desc_courte"].rstrip(".") + ". Livraison en Europe et dans le monde.")[:160]
    p["a_confirmer"] = "Liste INCI complète et PAO (ingrédients clés reçus) ; détourage HD à prévoir pour le Glow Oil (photo actuelle : flacon tenu en main)."

# ---------- Excel ----------
wb = Workbook()
ARIAL = "Arial"
H = Font(name=ARIAL, bold=True, color="FFFFFF")
HF = PatternFill("solid", fgColor="7A5C3E")
IN = PatternFill("solid", fgColor="FFF2CC")
thin = Border(bottom=Side(style="thin", color="DDDDDD"))

ws = wb.active
ws.title = "Produits"
cols = [("Référence", 10), ("Nom du produit", 34), ("Catégorie", 24), ("Contenance", 18), ("Prix (€)", 10), ("Prix promo (€)", 11),
        ("Stock (quantité)", 11), ("Disponibilité", 16), ("Badge", 13), ("Image principale", 55), ("Vidéo", 55), ("Texte ALT", 32),
        ("Description courte", 50), ("Description", 70), ("Conseils d'utilisation", 60), ("Ingrédients", 25), ("Slug (URL)", 32),
        ("Meta title", 40), ("Meta description", 60), ("À confirmer", 55)]
for c, (h, w) in enumerate(cols, 1):
    cell = ws.cell(row=1, column=c, value=h)
    cell.font, cell.fill = H, HF
    cell.alignment = Alignment(wrap_text=True, vertical="center")
    ws.column_dimensions[cell.column_letter].width = w
ws.row_dimensions[1].height = 32
for r, p in enumerate(P, 2):
    vals = [p["ref"], p["nom"], p["categorie"], p["contenance"], p["prix"], p.get("prix_promo"), None,
            f'=IF(G{r}="","À renseigner",IF(G{r}<=0,"Rupture de stock",IF(G{r}<=Paramètres!$B$4,"Stock limité","En stock")))',
            None, p["image"], p["video"] or "", p["alt"], p["desc_courte"], p["desc"], p["conseils"], p["ingredients"], p["slug"],
            p["meta_title"], p["meta_description"], p["a_confirmer"]]
    for c, v in enumerate(vals, 1):
        cell = ws.cell(row=r, column=c, value=v)
        cell.font = Font(name=ARIAL, size=10, color="0000FF" if c in (5, 6, 7) else "000000")
        cell.alignment = Alignment(wrap_text=c in (13, 14, 15, 20), vertical="top")
        cell.border = thin
    for c in (5, 6, 7, 9):
        ws.cell(row=r, column=c).fill = IN
    for c in (5, 6):
        ws.cell(row=r, column=c).number_format = '#,##0.00 "€";-#,##0.00 "€";-'
    ws.cell(row=r, column=20).fill = PatternFill("solid", fgColor="FCE4D6")
last = len(P) + 1
ws.freeze_panes = "C2"
dv = DataValidation(type="list", formula1='"Nouveauté,Best-seller,Promo,Coup de cœur"', allow_blank=True)
ws.add_data_validation(dv)
dv.add(f"I2:I{last}")
for txt, color in [("Rupture de stock", "F8CBAD"), ("Stock limité", "FFE699"), ("En stock", "C6EFCE")]:
    ws.conditional_formatting.add(f"H2:H{last}", FormulaRule(formula=[f'$H2="{txt}"'], fill=PatternFill("solid", fgColor=color)))

pa = wb.create_sheet("Paramètres")
rows = [
    ("Paramètres de la boutique", None, None),
    ("Les cellules jaunes sont à remplir ou modifier.", None, None),
    (None, None, None),
    ("Seuil « Stock limité » (quantité)", SEUIL_STOCK_LIMITE, "Hypothèse : à partir de 3 unités ou moins, le site affiche « Stock limité »."),
    ("Nom de domaine", "maisonfloriane.com", "Confirmé le 08/11/2026"),
    ("E-mail de contact", CONTACT["email"], "Transmis par la propriétaire le 08/11/2026"),
    ("Téléphone / WhatsApp", CONTACT["telephone"], "Transmis le 08/11/2026, sert aussi de WhatsApp (bouton de contact du site)"),
    ("Devise", "EUR (€)", "Prix TTC fixés le 09/11/2026 : savon noir 45,99 €, Glow Oil 35,99 €, duo 69,99 € au lieu de 81,98 €"),
    (None, None, None),
    ("Livraison", None, None),
    ("Zones desservies", "Monde entier, accent sur l'UE", "Expédition depuis l'Espagne ; hors UE : droits de douane à la charge du client"),
    ("Frais de livraison", None, "Par pays ou zone, seuil de livraison offerte éventuel"),
    ("Délais estimés", None, "Ex. 2 à 4 jours ouvrés France, 4 à 7 jours UE"),
    ("Transporteur", None, "Ex. Colissimo, Mondial Relay, DHL"),
    (None, None, None),
    ("Moyens de paiement", None, None),
    ("Carte bancaire / Apple Pay / Google Pay", "À confirmer", "WooCommerce Stripe Gateway (gratuit)"),
    ("PayPal", "À confirmer", "WooCommerce PayPal Payments (gratuit)"),
    ("WhatsApp", "Activé", "Commande par message, depuis n'importe quel pays"),
]
for r, row in enumerate(rows, 1):
    for c, v in enumerate(row, 1):
        pa.cell(row=r, column=c, value=v).font = Font(name=ARIAL, size=10, bold=(c == 1 and row[1] is None and v is not None and r > 2), italic=(r == 2))
    if r in (4, 7, 12, 13, 14, 17, 18):
        pa.cell(row=r, column=2).fill = IN
        pa.cell(row=r, column=2).font = Font(name=ARIAL, size=10, color="0000FF")
pa["A1"].font = Font(name=ARIAL, size=14, bold=True)
for c, w in zip("ABC", (38, 40, 75)):
    pa.column_dimensions[c].width = w

lg = wb.create_sheet("Mode d'emploi")
for line in [
    "Mode d'emploi du catalogue Maison Floriane",
    "",
    "• Onglet Produits : une ligne par produit (2 produits : savon noir et Glow Oil). Cellules jaunes = à remplir : Prix (€), Prix promo (€), Stock, Badge.",
    "• Chiffres en bleu = valeurs saisies à la main. La colonne Disponibilité se calcule seule à partir du stock.",
    "• Exemple : stock 10 → En stock ; stock 2 → Stock limité ; stock 0 → Rupture de stock.",
    "• Colonne « À confirmer » (orange) : points à vérifier avant la mise en ligne.",
    "• Ne pas modifier les colonnes Slug (URL) une fois le site en ligne : cela casserait les liens Google.",
]:
    lg.append([line])
lg["A1"].font = Font(name=ARIAL, size=14, bold=True)
for r in range(2, 8):
    lg.cell(row=r, column=1).font = Font(name=ARIAL, size=10)
lg.column_dimensions["A"].width = 120

wb.calculation.fullCalcOnLoad = True
wb.save(os.path.join(OUT, "catalogue-produits.xlsx"))

# ---------- JSON pour le site ----------
AA = [f for f in sorted(os.listdir(IMG + "avant-apres"))]
data = {
    "boutique": "Maison Floriane", "marque": "Floréa Skin", "domaine": "maisonfloriane.com", "contact": CONTACT,
    "devise": "EUR", "zone_livraison": "Monde entier (accent Union européenne)", "seuil_stock_limite": SEUIL_STOCK_LIMITE,
    "produits": [{
        "reference": p["ref"], "nom": p["nom"], "slug": p["slug"], "marque": p["marque"], "categorie": p["categorie"],
        "contenance": p["contenance"], "prix": p["prix"], "prix_promo": p.get("prix_promo"), "stock": None, "publie": True,
        "images": [{"src": p["image"], "alt": p["alt"]}],
        "video": {"src": p["video"], "poster": p["video"].replace(".mp4", "-apercu.webp")} if p["video"] else None,
        "description_courte": p["desc_courte"], "description": p["desc"], "conseils_utilisation": p["conseils"],
        "ingredients": p["ingredients"], "seo": {"title": p["meta_title"], "description": p["meta_description"]},
        "a_confirmer": p["a_confirmer"],
    } for p in P],
    "preuves_avant_apres": {
        "photos_avant": [IMG + "avant-apres/" + f for f in AA],
        "videos_resultat": [VID + "avant-apres/video-florea-skin-resultat-peau-sans-filtre-cape-noire.mp4",
                            VID + "avant-apres/video-florea-skin-resultat-peau-sans-filtre-voiture.mp4"],
    },
}
with open(os.path.join(OUT, "catalogue-produits.json"), "w", encoding="utf-8") as f:
    json.dump(data, f, ensure_ascii=False, indent=2)

paths = [p["image"] for p in P] + [p["video"] for p in P if p["video"]] + data["preuves_avant_apres"]["videos_resultat"]
print(len(P), "produits ; fichiers manquants :", [x for x in paths if not os.path.exists(x)])
