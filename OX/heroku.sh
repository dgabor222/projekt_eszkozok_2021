# htaccess modositasa a https forceolasa miatt
mv heroku-htaccess public/.htaccess

# DB beallitasa
touch database/database.sqlite
php artisan migrate:fresh --ansi
php artisan db:seed --ansi
