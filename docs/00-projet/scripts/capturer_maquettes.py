"""Captures pleine page du prototype (desktop et mobile) avec Edge headless piloté par le protocole DevTools.
L'émulation d'appareil permet un vrai rendu 390 px (Edge impose sinon une largeur de fenêtre minimale).
Usage : python 00-projet/scripts/capturer_maquettes.py  (depuis la racine du projet)"""
import base64, json, os, subprocess, tempfile, time, urllib.request
import websocket
from PIL import Image

EDGE = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
PROTO = os.path.abspath("03-maquettes/prototype")
OUT = "03-maquettes/captures"
PORT = 9333
os.makedirs(OUT, exist_ok=True)

# (page, paramètres, largeur, facteur d'échelle, mobile, nom, hauteur fixe ou None = pleine page)
CAPTURES = [
    ("index.html", "", 1440, 1, False, "accueil-desktop", None),
    ("savon-noir.html", "", 1440, 1, False, "fiche-produit-desktop", None),
    ("savon-noir.html", "panier", 1440, 1, False, "panier-desktop", 900),
    ("contact.html", "", 1440, 1, False, "contact-desktop", None),
    ("index.html", "", 390, 2, True, "accueil-mobile", None),
    ("savon-noir.html", "", 390, 2, True, "fiche-produit-mobile", None),
    ("savon-noir.html", "panier", 390, 2, True, "panier-mobile", 844),
    ("contact.html", "", 390, 2, True, "contact-mobile", None),
]

proc = subprocess.Popen([EDGE, "--headless=new", "--disable-gpu", "--hide-scrollbars", "--allow-file-access-from-files",
                         f"--remote-debugging-port={PORT}", f"--user-data-dir={tempfile.mkdtemp(prefix='edge-capture-')}",
                         "--no-first-run", "about:blank"])
try:
    for _ in range(50):
        try:
            cibles = json.load(urllib.request.urlopen(f"http://127.0.0.1:{PORT}/json"))
            page = next(c for c in cibles if c["type"] == "page")
            break
        except Exception:
            time.sleep(0.2)
    ws = websocket.create_connection(page["webSocketDebuggerUrl"], timeout=60, suppress_origin=True)
    n = 0

    def cdp(method, **params):
        global n
        n += 1
        ws.send(json.dumps({"id": n, "method": method, "params": params}))
        while True:
            msg = json.loads(ws.recv())
            if msg.get("id") == n:
                return msg.get("result", {})

    cdp("Page.enable")
    for fichier, params, w, dsf, mobile, nom, hauteur in CAPTURES:
        cdp("Emulation.setDeviceMetricsOverride", width=w, height=hauteur or 900, deviceScaleFactor=dsf, mobile=mobile)
        url = "file:///" + os.path.join(PROTO, fichier).replace("\\", "/") + ("?" + params if params else "")
        cdp("Page.navigate", url=url)
        time.sleep(2.5)  # polices Google + images
        cdp("Runtime.evaluate", expression="document.fonts.ready", awaitPromise=True)
        if hauteur is None:
            h = cdp("Runtime.evaluate", expression="document.documentElement.scrollHeight")["result"]["value"]
            cdp("Emulation.setDeviceMetricsOverride", width=w, height=h, deviceScaleFactor=dsf, mobile=mobile)
            time.sleep(0.6)
        largeur = cdp("Runtime.evaluate", expression="document.documentElement.scrollWidth")["result"]["value"]
        data = cdp("Page.captureScreenshot", format="jpeg", quality=86)["data"]
        chemin = os.path.join(OUT, nom + ".jpg")
        with open(chemin, "wb") as f:
            f.write(base64.b64decode(data))
        print(nom, Image.open(chemin).size, "débordement horizontal !" if largeur > w else "")
    ws.close()
finally:
    proc.terminate()
