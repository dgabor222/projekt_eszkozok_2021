:: Setup
copy .env.example .env
call php artisan key:generate
call composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
call npm install --quiet
call npm run --quiet prod
type nul > database/database.sqlite
call php artisan migrate:fresh
call php artisan db:seed
