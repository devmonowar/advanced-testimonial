# WordPress.org assets

Drop the WordPress.org plugin-directory assets here. The deploy workflow
(`.github/workflows/deploy.yml`, `ASSETS_DIR: .wordpress-org`) syncs them to the
SVN `assets/` folder on each tagged release.

Expected files:

- `icon-256x256.png` (or `icon.svg`)
- `banner-772x250.png` and `banner-1544x500.png`
- `screenshot-1.png`, `screenshot-2.png`, … (match the order in `readme.txt` Screenshots)
