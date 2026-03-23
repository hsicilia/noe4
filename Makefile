COMPOSE = docker compose --env-file .env.local -f docker-compose.yml
DEV_COMPOSE = $(COMPOSE) -f docker-compose.dev.yml
PROD_COMPOSE = $(COMPOSE) -f docker-compose.prod.yml
APP_ENV := $(shell grep '^APP_ENV=' .env.local | cut -d= -f2)
COMPOSE_CMD = $(if $(filter prod,$(APP_ENV)),$(PROD_COMPOSE),$(DEV_COMPOSE))

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo "Opciones disponibles (APP_ENV=$(APP_ENV)):"
	@echo ""
	@echo "  up               Arranca el entorno según APP_ENV"
	@echo "  down             Para el entorno según APP_ENV"
	@echo "  build            Reconstruye la imagen según APP_ENV"
	@echo "  update           composer install, cache, assets y migraciones"
	@echo "  exec             Ejecuta comando en el contenedor: make exec ARGS='ls'"
	@echo "  console          Ejecuta bin/console: make console ARGS='cache:warmup'"
	@echo "  ecs-check        Revisa estilo de código con ECS"
	@echo "  ecs-fix          Corrige estilo de código con ECS"
	@echo "  rector-check     Revisa refactorizaciones pendientes con Rector"
	@echo "  rector-fix       Aplica refactorizaciones con Rector"
	@echo "  db-import        Importa un dump: make db-import FILE=dump.sql"
	@echo "  db-dump          Exporta un dump: make db-dump FILE=dump.sql"
	@echo "  prod-deploy      Primera vez en producción: construye imagen y arranca"

.PHONY: up
up:
	$(COMPOSE_CMD) up -d

.PHONY: down
down:
	$(COMPOSE_CMD) down

.PHONY: build
build:
	$(COMPOSE_CMD) build

.PHONY: update
update:
	$(if $(filter prod,$(APP_ENV)),git pull,)
	$(COMPOSE_CMD) exec php composer install $(if $(filter prod,$(APP_ENV)),--no-dev --optimize-autoloader --no-interaction,)
	$(COMPOSE_CMD) exec php bin/console cache:clear
	$(COMPOSE_CMD) exec php bin/console asset-map:compile
	$(COMPOSE_CMD) exec php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: exec
exec:
	$(COMPOSE_CMD) exec php $(ARGS)

.PHONY: console
console:
	$(COMPOSE_CMD) exec php bin/console $(ARGS)

.PHONY: ecs-check
ecs-check:
	$(COMPOSE_CMD) exec php vendor/bin/ecs check

.PHONY: ecs-fix
ecs-fix:
	$(COMPOSE_CMD) exec php vendor/bin/ecs check --fix

.PHONY: rector-check
rector-check:
	$(COMPOSE_CMD) exec php vendor/bin/rector process --dry-run

.PHONY: rector-fix
rector-fix:
	$(COMPOSE_CMD) exec php vendor/bin/rector process

.PHONY: db-import
db-import:
	$(COMPOSE_CMD) exec -T db sh -c 'mariadb -u $$MARIADB_USER -p$$MARIADB_PASSWORD $$MARIADB_DATABASE' < $(FILE)

.PHONY: db-dump
db-dump:
	$(COMPOSE_CMD) exec -T db sh -c 'mariadb-dump -u $$MARIADB_USER -p$$MARIADB_PASSWORD $$MARIADB_DATABASE' > $(FILE)

.PHONY: prod-deploy
prod-deploy:
	$(PROD_COMPOSE) build
	$(PROD_COMPOSE) up -d
