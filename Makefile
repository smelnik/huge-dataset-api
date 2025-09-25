ENV = --env-file ./.docker/.env.local

build:
	docker compose ${ENV} build

up:
	docker compose ${ENV} up -d

stop:
	docker compose ${ENV} stop

down:
	docker compose ${ENV} down

ps:
	docker compose ${ENV} ps

restart:
	docker compose ${ENV} restart

vendor-refresh:
	docker compose ${ENV} exec php-fpm bash -c "rm -rf vendor/ var/ && composer i"

php-fpm:
	docker compose ${ENV} exec php-fpm bash

php-fpm-root:
	docker compose ${ENV} exec -u root php-fpm bash

php-clear:
	docker compose ${ENV} exec php-fpm bin/console cache:clear
	docker compose ${ENV} exec php-fpm bin/console cache:clear --env=test

dump-autoload:
	docker compose ${ENV} exec php-fpm composer dump-autoload

redis:
	docker compose ${ENV} exec redis sh

test-unit:
	docker compose ${ENV} exec php-fpm bash -c "vendor/bin/phpunit --colors=always --testdox"

test: test-unit
