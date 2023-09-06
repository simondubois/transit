include .env

deploy:
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && git fetch"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && git reset origin/master --hard"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 composer install --optimize-autoloader --no-dev"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 artisan config:cache"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 artisan view:cache"
	ssh ${DEPLOY_HOST} "cd ${DEPLOY_PATH} && php-8.2 artisan route:cache"

start:
	./vendor/bin/sail up -d

stop:
	./vendor/bin/sail stop
