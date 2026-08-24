"""Genera acg-visual/screenshot.png, la miniatura que WordPress muestra en
Apariencia → Temas.

Se escribe el PNG a mano (píxeles + zlib) porque el entorno no tiene Pillow y
no merece la pena añadir una dependencia para una imagen que se genera una vez.

La composición repite el hero: degradado cálido de atardecer, sol, siluetas y
el hexágono de la marca en naranja.

Uso: python _tools/make-screenshot.py
"""

import math
import os
import struct
import zlib

ANCHO, ALTO = 1200, 900
NARANJA = (250, 102, 19)
OSCURO = (10, 10, 10)


def mezcla(a, b, t):
    """Interpola dos colores."""
    t = max(0.0, min(1.0, t))
    return tuple(int(round(a[i] + (b[i] - a[i]) * t)) for i in range(3))


def dentro_silueta(x, y, cx, base, alto):
    """True si el punto cae dentro de una silueta de persona."""
    cabeza_r = alto * 0.085
    cabeza_cy = base - alto * 0.90
    if (x - cx) ** 2 + (y - cabeza_cy) ** 2 <= cabeza_r ** 2:
        return True

    # El cuerpo arranca por encima del centro de la cabeza para que hombros y
    # cabeza queden unidos: si empieza más abajo, la cabeza parece flotar.
    cuerpo_alto = alto * 0.88
    if base - cuerpo_alto <= y <= base:
        avance = (y - (base - cuerpo_alto)) / cuerpo_alto
        medio = alto * (0.055 + 0.085 * avance)
        return abs(x - cx) <= medio

    return False


def en_hexagono(x, y, cx, cy, radio, grosor):
    """True si el punto está sobre el trazo del hexágono de la marca."""
    dx, dy = x - cx, y - cy
    dist = math.hypot(dx, dy)

    # Sin pre-filtro por distancia: en el centro de cada lado el borde está a
    # la apotema (0,87 R), bastante más cerca que el radio, y descartar por
    # radio dejaría el hexágono partido en seis trocitos.
    if dist > radio + grosor:
        return False

    ang = math.atan2(dy, dx)
    # Distancia al borde de un hexágono regular con vértices arriba y abajo.
    sector = math.pi / 3
    a = (ang + math.pi / 6) % sector - sector / 2
    borde = radio * math.cos(sector / 2) / math.cos(a)

    if abs(dist - borde) > grosor:
        return False

    # Los costados verticales van cortados, igual que en la marca de agua.
    grados = math.degrees(ang) % 360
    for centro in (0, 180):
        if abs(((grados - centro + 180) % 360) - 180) < 19:
            return False

    return True


def construir():
    filas = bytearray()
    sol_x, sol_y = ANCHO * 0.66, ALTO * 0.42
    suelo = int(ALTO * 0.8)

    siluetas = [
        (ANCHO * 0.30, ALTO * 0.5),
        (ANCHO * 0.365, ALTO * 0.47),
    ]

    for y in range(ALTO):
        filas.append(0)  # filtro «none» por fila
        for x in range(ANCHO):
            if y >= suelo:
                color = OSCURO
            else:
                # Cielo: de casi negro arriba a naranja quemado abajo.
                t = y / suelo
                if t < 0.55:
                    color = mezcla((26, 20, 16), (90, 44, 18), t / 0.55)
                else:
                    color = mezcla((90, 44, 18), (194, 90, 28), (t - 0.55) / 0.45)

                # Halo del sol.
                d = math.hypot(x - sol_x, y - sol_y)
                halo = max(0.0, 1 - d / (ANCHO * 0.30))
                if halo > 0:
                    color = mezcla(color, (255, 176, 102), halo ** 2.2 * 0.85)
                if d < ANCHO * 0.045:
                    color = mezcla(color, (255, 214, 168), 0.9)

            for cx, alto in siluetas:
                if dentro_silueta(x, y, cx, suelo, alto):
                    color = (5, 5, 5)
                    break

            if en_hexagono(x, y, ANCHO * 0.80, ALTO * 0.58, ALTO * 0.15, 3.0):
                color = NARANJA

            # Viñeta suave en los bordes, como en el hero.
            if y < ALTO * 0.18:
                color = mezcla(color, OSCURO, (1 - y / (ALTO * 0.18)) * 0.55)

            filas.extend(color)

    return bytes(filas)


def chunk(tipo, datos):
    return (
        struct.pack(">I", len(datos))
        + tipo
        + datos
        + struct.pack(">I", zlib.crc32(tipo + datos) & 0xFFFFFFFF)
    )


def main():
    raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    destino = os.path.join(raiz, "acg-visual", "screenshot.png")

    cabecera = struct.pack(">IIBBBBB", ANCHO, ALTO, 8, 2, 0, 0, 0)
    png = (
        b"\x89PNG\r\n\x1a\n"
        + chunk(b"IHDR", cabecera)
        + chunk(b"IDAT", zlib.compress(construir(), 9))
        + chunk(b"IEND", b"")
    )

    with open(destino, "wb") as archivo:
        archivo.write(png)

    print(f"screenshot.png generado ({len(png) // 1024} KB)")


if __name__ == "__main__":
    main()
