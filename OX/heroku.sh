# htaccess modositasa a https forceolasa miatt
mv heroku-htaccess public/.htaccess

# DB beallitasa
touch database/database.sqlite
php artisan migrate:fresh --ansi --no-interaction
php artisan db:seed --ansi --no-interaction
