#!/bin/bash
set -euo pipefail
P="$(cd "$(dirname "$0")/.." && pwd)"; D="$P/deploy"
PHP83="${PHP83:-php}"
OUT="${OUT:-$HOME/Desktop/namtech-bluehost.zip}"
W="$(mktemp -d)"; trap 'rm -rf "$W"' EXIT
S="$W/s"; mkdir -p "$S/database"
echo "▸ Building design"; (cd "$P/frontend" && npm run build >/dev/null 2>&1)
echo "▸ Copying backend"
rsync -a --exclude .env --exclude '.env.*' --exclude vendor --exclude node_modules --exclude .DS_Store --exclude .phpunit.result.cache \
  --exclude '/storage/logs/*.log' --exclude '/storage/framework/cache/data/*' --exclude '/storage/framework/sessions/*' --exclude '/storage/framework/views/*' \
  --exclude '/storage/framework/testing' --exclude '/bootstrap/cache/*.php' --exclude '/bootstrap/ssr' --exclude '/public/storage' --exclude '/public/hot' \
  --exclude '*.map' \
  "$P/backend/" "$S/backend/"
mkdir -p "$S/backend/storage/framework/"{cache/data,sessions,views} "$S/backend/storage/logs"
rsync -a --exclude node_modules --exclude .DS_Store --exclude dist "$P/frontend/" "$S/frontend/"
echo "▸ PHP packages (PHP 8.3, no dev)"
(cd "$S/backend" && "$PHP83" "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction --quiet)
echo "▸ Database"
g() { grep -E "^$1=" "$P/backend/.env" | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
DB="$(g DB_DATABASE)"; export MYSQL_PWD="$(g DB_PASSWORD)"
C=(-h "$(g DB_HOST)" -P "$(g DB_PORT)" -u "$(g DB_USERNAME)")
SKIP=(cache cache_locks sessions jobs job_batches failed_jobs password_reset_tokens)
KEEP=(enquiries job_applications newsletter_subscribers)
IG=(); for t in "${SKIP[@]}" "${KEEP[@]}"; do IG+=("--ignore-table=$DB.$t"); done
EX=(); for t in "${SKIP[@]}"; do mysql "${C[@]}" -N -e "SHOW TABLES LIKE '$t'" "$DB" | grep -q . && EX+=("$t"); done
DUMP=(--single-transaction --set-gtid-purged=OFF --no-tablespaces --skip-comments --skip-add-locks --default-character-set=utf8mb4)
{ echo 'SET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS=0;'
  mysqldump "${C[@]}" "${DUMP[@]}" "${IG[@]}" "$DB"
  mysqldump "${C[@]}" "${DUMP[@]}" --no-data "$DB" "${EX[@]}"
  mysqldump "${C[@]}" "${DUMP[@]}" --no-data --skip-add-drop-table "$DB" "${KEEP[@]}" | sed 's/^CREATE TABLE `/CREATE TABLE IF NOT EXISTS `/'
  echo 'SET FOREIGN_KEY_CHECKS=1;'; } > "$S/database/namtech.sql"
sed -i '' -E "s#/\*!800[0-9]{2} DEFAULT ENCRYPTION='N' \*/##g" "$S/database/namtech.sql"; unset MYSQL_PWD
echo "▸ Pruning old build files"
python3 -I - "$S/backend/public/build" <<'PY'
import json, os, sys
b = sys.argv[1]; keep = set()
for e in json.load(open(f'{b}/manifest.json')).values():
    keep.add(e['file']); keep.update(e.get('css', [])); keep.update(e.get('assets', []))
for n in os.listdir(f'{b}/assets'):
    if f'assets/{n}' not in keep: os.remove(f'{b}/assets/{n}')
PY
echo "▸ Server files"
KEY="base64:$(openssl rand -base64 32)"
sed "s#^APP_KEY=.*#APP_KEY=$KEY#" "$D/env.template" > "$S/backend/.env"
cp "$D/htaccess" "$S/.htaccess"
cp "$D/README.md" "$S/README-BLUEHOST.md"
date -u +%Y%m%d-%H%M%S > "$S/backend/release.txt"
rm -f "$OUT"; (cd "$S" && zip -qry -9 "$OUT" .)
ls -lh "$OUT"
