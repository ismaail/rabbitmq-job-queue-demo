.PHONY: up start stop down

# Set dir of Makefile to a variable to use later
MAKEPATH := $(abspath $(lastword $(MAKEFILE_LIST)))
CONTAINER := "rabbitmq_supervisor"

up:
	docker-compose up -d

start:
	docker-compose start

stop:
	docker-compose stop

down:
	docker-compose down

fix-permissions:
	docker exec -it $(CONTAINER) chown -R 1000:100 ./src/logs 2>/dev/null || true && \
	docker exec -it $(CONTAINER) chown -R 1000:100 ./vendor 2>/dev/null || true && \
	docker exec -it $(CONTAINER) chown    1000:100 ./composer.lock 2>/dev/null || true
