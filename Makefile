include .env

all: check test

check: php-cs-fixer phpcs phpmd phpstan

deploy:
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && git fetch"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && git reset origin/master --hard"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 composer install --optimize-autoloader --no-dev"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 artisan config:cache"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 artisan view:cache"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 artisan route:cache"

php-cs-fixer:
	composer exec -- php-cs-fixer fix --quiet

phpcs:
	composer exec -- phpcs . -s --cache

phpmd:
	composer exec -- phpmd . text phpmd.xml --cache

phpstan:
	composer exec -- phpstan analyze -c phpstan.neon --no-progress

start:
	./vendor/bin/sail up -d

stop:
	./vendor/bin/sail stop

test:
	php artisan test
