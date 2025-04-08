#!make
# Makefile for Tasker Project

# Default target when make is run without arguments
default: help

# ====================================================================================
# Docker Compose Commands
# ====================================================================================

build: ## Build or rebuild services
	docker compose build

ps: ## List running containers
	docker compose ps

up: ## Start services in detached mode (db, redis, app, horizon, schedule, webserver)
	docker compose up -d db redis app horizon webserver

down: ## Stop services
	docker compose down

down-volumes: ## Stop services and remove volumes
	docker compose down --volumes

restart: ## Restart running services (db, redis, app, horizon, schedule, webserver)
	docker compose restart db redis app horizon schedule webserver

logs: ## Tail logs for specified service (e.g., make logs service=app)
	docker compose logs -f $(service)

logs-app: ## Tail logs for the 'app' (Octane) service
	docker compose logs -f app

logs-horizon: ## Tail logs for the 'horizon' service
	docker compose logs -f horizon

restart-horizon: ## Restart the 'horizon' service
	docker compose restart horizon

restart-schedule: ## Restart the 'schedule' service
	docker compose restart schedule

# ====================================================================================
# Application Initialization & Setup
# ====================================================================================

init: ## Initialize project: copy .env if it doesn't exist
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo ".env file created. Please review and fill APP_KEY."; \
	else \
		echo ".env file already exists."; \
	fi

key: ## Generate Laravel application key (if APP_KEY is empty in .env)
	@if grep -q '^APP_KEY=$' .env; then \
		docker compose run --rm artisan key:generate --show | awk 'NR==1{print $$1}' | xargs -I {} sed -i -e 's/^APP_KEY=$$/APP_KEY={}/' .env; \
		echo "APP_KEY generated in .env"; \
	else \
		echo "APP_KEY already set in .env or .env file missing."; \
	fi

composer-install: ## Install PHP dependencies using Composer
	docker compose run --rm composer install $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

npm-install: ## Install Node.js dependencies using NPM
	docker compose run --rm npm install $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

npm-build: ## Compile frontend assets for production
	docker compose run --rm npm run build

npm-dev: ## Compile frontend assets and watch for changes
	docker compose run --rm --service-ports npm run dev -- --host

migrate: ## Run database migrations
	docker compose run --rm artisan migrate

migrate-fresh: ## Drop all tables and re-run all migrations
	docker compose run --rm artisan migrate:fresh

seed: ## Seed the database with records (specify seeder class if needed)
	docker compose run --rm artisan db:seed $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

# ====================================================================================
# Tooling Commands (Composer, NPM, Artisan)
# ====================================================================================

composer: ## Run Composer commands (e.g., make composer require laravel/pint)
	docker compose run --rm composer $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

npm: ## Run NPM commands (e.g., make npm run build)
	docker compose run --rm npm $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

art: ## Run Artisan commands (e.g., make art route:list)
	docker compose run --rm artisan $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

tinker: ## Start Laravel Tinker session
	docker compose run --rm artisan tinker

# ====================================================================================
# Common Laravel Generator Commands
# ====================================================================================

migration: ## Make a new migration file (e.g., make migration create_posts_table)
	docker compose run --rm artisan make:migration $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

model: ## Make a new Eloquent model (e.g., make model Post -mfs)
	docker compose run --rm artisan make:model $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

controller: ## Make a new controller (e.g., make controller PostController --api --model=Post)
	docker compose run --rm artisan make:controller $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

resource: ## Make a new API resource (e.g., make resource PostResource)
	docker compose run --rm artisan make:resource $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

request: ## Make a new form request (e.g., make request StorePostRequest)
	docker compose run --rm artisan make:request $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

# ====================================================================================
# Code Quality & Testing
# ====================================================================================

pint: ## Format PHP code using Laravel Pint
	docker compose run --rm --entrypoint php artisan ./vendor/bin/pint $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

test: ## Run PHPUnit/Pest tests
	docker compose run --rm artisan test $(filter-out $@,$(MAKECMDGOALS))
%:
	@:

swagger: ## Generate OpenAPI/Swagger documentation (requires setup)
	docker compose run --rm artisan l5-swagger:generate --all

phpstan:
	docker compose run --rm --entrypoint php artisan vendor/bin/phpstan analyse --memory-limit=1G

insights: ## Run PHP Insights analysis
	docker compose run --rm artisan insights

# ====================================================================================
# Help Target - Generates help message based on comments
# ====================================================================================
help: ## Show this help message
	@echo ""
	@echo "Usage: make [command]"
	@echo ""
	@echo "Available commands:"
	@grep -E '^[a-zA-Z0-9_\-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "$(shell tput setaf 6)%-20s$(shell tput sgr0) %s\n", $$1, $$2}' | \
		sort
	@echo ""

# Phony targets prevent conflicts with file names
# .PHONY: default help build ps up down down-volumes restart logs logs-app logs-horizon restart-horizon restart-schedule \
# 	init key composer-install npm-install npm-build npm-dev migrate migrate-fresh seed \
# 	composer npm art tinker \
# 	migration model controller resource request \
# 	pint test swagger