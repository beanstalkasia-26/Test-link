# Deploying NAMTECH to Bluehost

`bash deploy/build.sh` builds `namtech-bluehost.zip` on the Desktop. It needs PHP 8.3 (`PHP83=/path/to/php`), Composer,
Node, MySQL client tools and the local database from `backend/.env`. The zip holds the backend with its PHP packages and
built design, `database/namtech.sql`, the images and a ready `backend/.env`.

Before building, set `APP_URL` in `deploy/env.template` to the live address.

## Upload
1. cPanel → MySQL Databases: create a database and a user with ALL PRIVILEGES. Names carry a prefix.
2. cPanel → MultiPHP Manager: PHP 8.3 for the domain.
3. File Manager: open the domain's document root (cPanel → Domains), upload and extract the zip there, delete the zip.
4. Edit `backend/.env`: `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Save.
5. Open the site. The first visit imports the content and builds caches (10-60 seconds).

New uploads replace the content in the database. Enquiries, job applications and newsletter sign-ups are kept.

## Troubleshooting
| Problem | Fix |
|---|---|
| 404 | The files must sit directly in the domain's document root, with `.htaccess` beside `backend/`. |
| "Almost there" | The database values in `backend/.env` are missing or wrong. |
| "The update could not be applied" | Read `backend/storage/logs/deploy.log`. |
| 500 error or blank page | PHP must be 8.3; read the newest file in `backend/storage/logs/`. |
