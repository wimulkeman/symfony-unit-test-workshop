SHELL=/bin/bash
UNAME_S := $(shell uname -s)
ifeq ($(UNAME_S),Darwin)
	CACHED_FLAG=:cached
endif

export HOST_UID=$(shell id -u)
export HOST_GID=$(shell id -g)

.PHONY: bootstrap
bootstrap:
ifneq ("$(wildcard composer.json)","")
	$(info ==> Installing composer dependencies...)
	cd qlico && docker-compose exec php sh -c "composer install --no-interaction --no-scripts"
endif

.PHONY: build-php-base
build-php-base:
	docker build . --target base

.PHONY: build-php-dev
build-php-dev:
	docker build . --target dev

.PHONY: setup
setup: build-php-dev bootstrap

.PHONY: up
up:
	cd qlico && docker-compose up -d

.PHONY: down
down:
	cd qlico && docker-compose down

.PHONY: shell
shell:
	cd qlico && docker-compose exec php sh
