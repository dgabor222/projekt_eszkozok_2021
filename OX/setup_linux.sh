cp .env.example .env
php artisan key:generate
composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
npm install --quiet
npm run --quiet prod
chmod -R 777 storage bootstrap/cache
touch database/database.sqlite
php artisan migrate:fresh
php artisan db:seed
