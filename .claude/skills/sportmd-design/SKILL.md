---
name: sportmd-design
description: Reguli de design pentru platforma Sport.md. Folosește-l la orice lucru de interfață (Blade, CSS, componente, pagini).
---

# Sport.md: reguli de design

Stil inspirat de Apple, mobile-first, alb și spațios. Detalii complete în docs/DESIGN.md.
Aceste reguli au prioritate față de `frontend-design` (care împinge spre alegeri „distinctive”).

## Înainte să scrii cod
1. Citește docs/DESIGN.md (secțiunile 3, 4 și 6).
2. Verifică în public/assets/css/tokens.css dacă variabila de care ai nevoie există. Dacă nu, întreabă înainte să o adaugi.
3. Construiește întâi vederea de 390px, apoi extinde.

## Reguli
- Culori, spațiere, colțuri: doar din tokens.css.
- Font Inter; pictograme Lucide (SVG).
- Culoarea sportului: fundal/bandă/pictogramă. Nu text mic.
- Fiecare sport are pictogramă ȘI nume, nu doar culoare.
- Butoane și ținte tactile minim 44px.
- Stări obligatorii: încărcare (skeleton), gol (cu acțiune), eroare (cu soluție), focus vizibil.
- Text în fișiere lang/ (RO + RU).
- Mișcare scurtă și doar ca răspuns la acțiune; respectă prefers-reduced-motion.
- Nu atinge logica din backend în timpul redesignului.

## Verificare la final
Verifică la 360, 390, 768, 1280px. Contrast text minim 4.5:1. Navigare completă cu tastatura.
