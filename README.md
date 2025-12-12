# Virtual Hosts Manager

Web application for managing Nginx virtual hosts via Docker containers.

**Tech Stack**: Laravel 12, PHP 8.2, Vue 3, Inertia.js, Tailwind CSS, Docker

## Quick Start

```bash
# 1. Clone repository
git clone https://github.com/wwwlopment/virtual-hosts-manager.git
cd virtual-hosts-manager-master

# 2. Get the Docker GID using a command and set it as the DOCKER_GID variable in your root .env file.
echo "DOCKER_GID=$(stat -c '%g' /var/run/docker.sock)" > .env

# 3. Start containers (everything configures automatically)
docker compose up -d --build

# 4. Open in browser
http://0.0.0.0:8080
# Login: test@test.com / testtest
```

**That's it!**

The entrypoint script automatically:
- Copies `.env.example` to `.env` in Laravel app
- Installs Composer dependencies
- Generates `APP_KEY`
- Creates storage symlink

## Features

- Create/delete Nginx virtual hosts dynamically
- Enable/disable hosts without deletion
- Control Nginx container (start/stop/restart/reload)
- Real-time container status monitoring
- Automatic config generation
- Port management (8081-8100)

## Docker Containers

- `nginx-manager-app` - Laravel PHP-FPM (connects to Docker socket)
- `nginx-manager-nginx-app` - Nginx for Laravel app (port 8080)
- `nginx-manager-nginx-hosts` - Nginx for virtual hosts (ports 8081-8100)
- `nginx-manager-node` - Vite dev server (port 5173)

## Configuration


## API Endpoints

**Virtual Hosts:**
- `GET /api/hosts` - List all
- `POST /api/hosts` - Create new
- `PATCH /api/hosts/{id}/status` - Enable/disable
- `DELETE /api/hosts/{id}` - Delete

**Nginx Control:**
- `GET /api/nginx/status` - Container status
- `POST /api/nginx/start` - Start container
- `POST /api/nginx/stop` - Stop container
- `POST /api/nginx/restart` - Restart container
- `POST /api/nginx/reload` - Reload config

## Useful Commands

```bash
# View logs
make logs

# Stop containers
make down

# Restart all
make restart

# Enter PHP container
make php

# See all available commands
make help
```

## License

MIT
