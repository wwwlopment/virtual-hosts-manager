# Makefile readme (en): <https://www.gnu.org/software/make/manual/html_node/index.html#SEC_Contents>

SHELL = /bin/bash

APP_CONTAINER_NAME := nginx-manager-app
NODE_CONTAINER_NAME := nginx-manager-node
NGINX_APP_CONTAINER_NAME := nginx-manager-nginx-app
NGINX_HOSTS_CONTAINER_NAME := nginx-manager-nginx-hosts

.PHONY: help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help

# === Docker commands ===

up: ## Start all containers
	docker compose up -d

build: ## Build and start containers (auto-setup)
	docker compose up -d --build

build-app: ## Rebuild only app container
	docker-compose build app
	docker-compose up -d app

build-nginx-app: ## Rebuild only nginx container
	docker-compose build nginx-app
	docker-compose up -d nginx-app

down: ## Stop all containers
	docker-compose down

restart: down up ## Restart all containers

logs: ## Show logs from all containers
	docker-compose logs -f

logs-app: ## Show logs from app container
	docker-compose logs -f app

logs-nginx-app: ## Show logs from nginx app container
	docker-compose logs -f nginx-app

logs-nginx-hosts: ## Show logs from nginx hosts container
	docker-compose logs -f nginx-hosts
# === Shell access ===

php: ## Enter php container
	@docker exec -w /var/www/html -it $(APP_CONTAINER_NAME) bash

php-root: ## Enter php container as root
	@docker exec -u root -w /var/www/html -it $(APP_CONTAINER_NAME) bash

node: ## Enter node container
	@docker exec -it $(NODE_CONTAINER_NAME) bash

nginx-app: ## Enter nginx-app container
	@docker exec -it $(NGINX_APP_CONTAINER_NAME) bash

nginx-hosts: ## Enter nginx-hosts container
	@docker exec -it $(NGINX_HOSTS_CONTAINER_NAME) bash

nginx-watch-logs: ## Watch nginx config watcher logs in real-time
	docker-compose logs -f nginx | grep -E "watch|reload|Config"
