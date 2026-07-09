# Sport cover images

Each sport has its own folder here, named after the sport's **slug**
(e.g. `football/`, `table-tennis/`). The room cards on the landing page show the
cover image found in the matching folder.

## How to set your own photo for a sport

Drop a file named **`cover`** into the sport's folder, using any of these extensions
(checked in this order):

```
cover.jpg  →  cover.jpeg  →  cover.png  →  cover.webp  →  cover.svg
```

Example: to set your own football photo, save it as:

```
public/assets/images/sports/football/cover.jpg
```

The card will use it automatically — no code change needed. The bundled `cover.svg`
files are placeholders; your `cover.jpg`/`.png`/`.webp` will take priority over them.

If a sport has no folder/image (e.g. a new sport added later from the admin panel),
the shared fallback `default.svg` is used.

Recommended photo size: ~800×360 px (cards crop to a 20:9 banner).
