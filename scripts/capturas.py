"""
capturas.py - Toma las capturas del portafolio desde tu propio osTicket.

Requisitos (una sola vez):
    pip install playwright
    python -m playwright install chromium

Uso:
    python scripts/capturas.py --url http://localhost/osticket --usuario otto.admin
    (la contraseña se pide en pantalla y no se guarda en ningún lado)

Guarda las imágenes en la carpeta capturas/ del repositorio.
"""
import argparse
import getpass
import pathlib
import re
import sys

from playwright.sync_api import sync_playwright

RAIZ = pathlib.Path(__file__).resolve().parent.parent
SALIDA = RAIZ / "capturas"


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--url", default="http://localhost/osticket")
    ap.add_argument("--usuario", required=True)
    ap.add_argument("--desde", default="02/11/2026", help="inicio del periodo del dashboard, en el formato de fecha de osTicket (dd/mm/aaaa)")
    ap.add_argument("--chrome", action="store_true", help="usar el Google Chrome instalado en vez de Chromium")
    a = ap.parse_args()
    clave = getpass.getpass(f"Contraseña de {a.usuario}: ")
    base = a.url.rstrip("/")
    SALIDA.mkdir(exist_ok=True)

    with sync_playwright() as p:
        nav = p.chromium.launch(channel="chrome" if a.chrome else None)
        pag = nav.new_page(viewport={"width": 1366, "height": 900}, locale="es-GT")

        def foto(nombre, completa=False):
            # La barra fija del ticket tapa el hilo en capturas de página completa
            pag.add_style_tag(content=".sticky.bar, .sticky.bar.fixed {position: static !important}")
            ruta = SALIDA / nombre
            pag.screenshot(path=str(ruta), full_page=completa)
            print("  guardada", ruta.relative_to(RAIZ))

        # Inicio de sesión en el panel de agentes
        pag.goto(f"{base}/scp/login.php")
        pag.fill("#name", a.usuario)
        pag.fill("#pass", clave)
        pag.click("button[name=submit]")
        pag.wait_for_load_state("networkidle")
        if "login.php" in pag.url:
            sys.exit("No se pudo iniciar sesión: revisa usuario y contraseña.")

        # 1. Versión instalada (reemplaza la captura del instalador si no la tomaste)
        pag.goto(f"{base}/scp/system.php")
        foto("01-informacion-del-sistema.png")
        # 2. Temas de ayuda y planes de SLA
        pag.goto(f"{base}/scp/helptopics.php")
        foto("02a-temas-de-ayuda.png")
        pag.goto(f"{base}/scp/slas.php")
        foto("02b-planes-sla.png")
        # 3. Lista de tickets (cola Closed = los 20 resueltos; en una instalación nueva es la cola 8)
        pag.goto(f"{base}/scp/tickets.php?queue=8")
        foto("03-lista-de-tickets.png", completa=True)

        def abrir_ticket(asunto):
            pag.goto(f"{base}/scp/tickets.php?queue=8")
            pag.get_by_role("link", name=re.compile(re.escape(asunto))).first.click()
            pag.wait_for_load_state("networkidle")

        # 4. Ticket completo con hilo y nota interna
        abrir_ticket("La impresora no imprime")
        foto("04-ticket-completo.png", completa=True)
        # 5. Ticket escalado a Nivel 2
        abrir_ticket("El antivirus detectó una amenaza")
        foto("05-ticket-escalado.png", completa=True)
        # 6. Respuestas predefinidas
        pag.goto(f"{base}/scp/canned.php")
        foto("06-respuestas-predefinidas.png")
        # 8. Dashboard del periodo simulado
        pag.goto(f"{base}/scp/dashboard.php")
        try:
            pag.eval_on_selector("input[name=start]", "(el, v) => el.value = v", a.desde)
            pag.select_option("select[name=period]", "+14 days")
            pag.click("#basic_search button[type=submit]")
            pag.wait_for_load_state("networkidle")
        except Exception:
            pass   # si cambian los nombres del formulario, se toma tal cual
        foto("08-dashboard.png", completa=True)

        # 7. Base de conocimiento vista como usuario (sesión nueva, sin login)
        cliente = nav.new_page(viewport={"width": 1366, "height": 900})
        cliente.goto(f"{base}/kb/index.php")
        cliente.screenshot(path=str(SALIDA / "07-base-de-conocimiento-portal.png"), full_page=True)
        print("  guardada capturas/07-base-de-conocimiento-portal.png")
        nav.close()
    print("Listo. Revisa cada imagen antes de subirla: no deben verse contraseñas ni datos reales.")


if __name__ == "__main__":
    main()
