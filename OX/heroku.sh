# Laravel Mix futtatasa
npm run prod

# DB beallitasa
touch database/database.sqlite
php artisan migrate:fresh --ansi
php artisan db:seed --ansi
