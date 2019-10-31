web: heroku-php-nginx -C heroku/nginx.conf public/
worker: php bin/console messenger:consume -vv --time-limit=3600
release: php bin/console doctrine:migrations:migrate --no-interaction
