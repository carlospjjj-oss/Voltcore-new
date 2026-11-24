#!/bin/bash
# =============================================================================
# VoltCore New — Setup Script para Ubuntu 22.04 / 24.04 / 26.10
# Instala: PHP 8.3, Laravel 11, MySQL 8, Nginx, Redis, Node.js 20, Composer
# Uso: sudo bash setup.sh
# =============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()    { echo -e "${GREEN}[✔]${NC} $1"; }
warn()   { echo -e "${YELLOW}[!]${NC} $1"; }
error()  { echo -e "${RED}[✘]${NC} $1"; exit 1; }
header() { echo -e "\n${BLUE}══════════════════════════════════════${NC}"; echo -e "${BLUE}  $1${NC}"; echo -e "${BLUE}══════════════════════════════════════${NC}"; }

# Verificar root
[ "$EUID" -ne 0 ] && error "Execute como root: sudo bash setup.sh"

# Configurações
APP_NAME="voltcore"
APP_DIR="/var/www/${APP_NAME}"
DB_NAME="voltcore"
DB_USER="voltcore_user"
DB_PASS=$(openssl rand -base64 20 | tr -d '=+/' | cut -c1-20)
DB_ROOT_PASS=$(openssl rand -base64 20 | tr -d '=+/' | cut -c1-20)
PHP_VERSION="8.3"
NODE_VERSION="20"
NGINX_CONF="/etc/nginx/sites-available/${APP_NAME}"

header "VoltCore New — Setup Debian 13"
echo "  App:      $APP_DIR"
echo "  DB Name:  $DB_NAME"
echo "  DB User:  $DB_USER"
echo ""

# =============================================================================
# 1. ATUALIZAR SISTEMA
# =============================================================================
header "1/9 — Atualizando sistema"

export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq \
    curl wget git unzip zip \
    software-properties-common \
    apt-transport-https ca-certificates \
    gnupg lsb-release \
    build-essential \
    openssl \
    cron

log "Sistema atualizado"

# =============================================================================
# 2. PHP 8.3
# =============================================================================
header "2/9 — Instalando PHP ${PHP_VERSION}"

# Adicionar repositório ondrej/php (funciona no Ubuntu 22.04, 24.04, 26.10)
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-dom

# Configurar php.ini para produção
PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 50M/' "$PHP_INI"
sed -i 's/^post_max_size.*/post_max_size = 50M/' "$PHP_INI"
sed -i 's/^memory_limit.*/memory_limit = 256M/' "$PHP_INI"
sed -i 's/^max_execution_time.*/max_execution_time = 120/' "$PHP_INI"
sed -i 's/^;date.timezone.*/date.timezone = America\/Sao_Paulo/' "$PHP_INI"

systemctl enable php${PHP_VERSION}-fpm
systemctl start php${PHP_VERSION}-fpm

log "PHP ${PHP_VERSION} instalado"

# =============================================================================
# 3. COMPOSER
# =============================================================================
header "3/9 — Instalando Composer"

EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
    warn "Checksum inválido, usando download direto"
fi

php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php

log "Composer $(composer --version | awk '{print $3}') instalado"

# =============================================================================
# 4. MYSQL 8
# =============================================================================
header "4/9 — Instalando MySQL 8"

apt-get install -y -qq mysql-server

# Iniciar MySQL
systemctl enable mysql
systemctl start mysql

# Configurar root e banco
mysql -u root <<SQL
-- Configurar root com senha
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASS}';

-- Criar banco e usuário da aplicação
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';

-- Usuário somente leitura para relatórios (opcional)
CREATE USER IF NOT EXISTS '${DB_USER}_read'@'localhost' IDENTIFIED BY '${DB_PASS}_ro';
GRANT SELECT ON \`${DB_NAME}\`.* TO '${DB_USER}_read'@'localhost';

FLUSH PRIVILEGES;
SQL

# Ajustar configurações MySQL para produção
cat > /etc/mysql/conf.d/voltcore.cnf <<EOF
[mysqld]
character-set-server  = utf8mb4
collation-server      = utf8mb4_unicode_ci
max_connections       = 200
innodb_buffer_pool_size = 256M
slow_query_log        = 1
slow_query_log_file   = /var/log/mysql/slow.log
long_query_time       = 2
sql_mode              = "STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO"

[client]
default-character-set = utf8mb4
EOF

systemctl restart mysql

log "MySQL 8 configurado (banco: ${DB_NAME}, user: ${DB_USER})"

# =============================================================================
# 5. REDIS
# =============================================================================
header "5/9 — Instalando Redis"

apt-get install -y -qq redis-server

# Configurar Redis
sed -i 's/^# maxmemory <bytes>/maxmemory 128mb/' /etc/redis/redis.conf
sed -i 's/^# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
sed -i 's/^supervised no/supervised systemd/' /etc/redis/redis.conf

systemctl enable redis-server
systemctl start redis-server

log "Redis instalado"

# =============================================================================
# 6. NGINX
# =============================================================================
header "6/9 — Instalando Nginx"

apt-get install -y -qq nginx

# Remover default
rm -f /etc/nginx/sites-enabled/default

# Criar vhost para o app
cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name _;

    root ${APP_DIR}/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/${APP_NAME}_access.log;
    error_log  /var/log/nginx/${APP_NAME}_error.log;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    # Segurança
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    server_tokens off;

    # Upload max
    client_max_body_size 50M;

    # Laravel — rota principal
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP-FPM
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    # Bloquear acesso a arquivos sensíveis
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache de assets estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/${APP_NAME}

nginx -t
systemctl enable nginx
systemctl start nginx

log "Nginx configurado"

# =============================================================================
# 7. NODE.JS 20 + NPM
# =============================================================================
header "7/9 — Instalando Node.js ${NODE_VERSION}"

curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
apt-get install -y -qq nodejs

npm install -g npm@latest 2>/dev/null || true

log "Node.js $(node --version) / npm $(npm --version) instalados"

# =============================================================================
# 8. CRIAR PROJETO LARAVEL 11
# =============================================================================
header "8/9 — Criando projeto Laravel 11"

mkdir -p /var/www
cd /var/www

if [ -d "$APP_DIR" ]; then
    warn "Diretório $APP_DIR já existe, pulando criação do projeto"
else
    composer create-project laravel/laravel ${APP_NAME} --prefer-dist --no-interaction 2>&1 | tail -5
fi

cd "$APP_DIR"

# Instalar pacotes essenciais
composer require \
    laravel/sanctum \
    spatie/laravel-permission \
    spatie/laravel-activitylog \
    barryvdh/laravel-dompdf \
    maatwebsite/excel \
    league/flysystem-aws-s3-v3 \
    predis/predis \
    --no-interaction 2>&1 | tail -5

# Instalar pacotes de dev
composer require --dev \
    laravel/telescope \
    barryvdh/laravel-debugbar \
    --no-interaction 2>&1 | tail -5

# Frontend — Vite + Tailwind + Alpine.js
npm install --save-dev \
    tailwindcss \
    @tailwindcss/forms \
    @tailwindcss/typography \
    autoprefixer \
    postcss \
    alpinejs \
    chart.js \
    @heroicons/vue

# Configurar .env
APP_KEY=$(php artisan key:generate --show 2>/dev/null || echo "")
cat > "${APP_DIR}/.env" <<DOTENV
APP_NAME="VoltCore"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=daily
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=redis
SESSION_LIFETIME=480

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@voltcore.com.br"
MAIL_FROM_NAME="VoltCore"
DOTENV

# Gerar chave
php artisan key:generate --force

# Permissões
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap/cache"

# Link de storage
php artisan storage:link 2>/dev/null || true

log "Laravel 11 criado em $APP_DIR"

# =============================================================================
# 9. CONFIGURAÇÕES FINAIS
# =============================================================================
header "9/9 — Configurações finais"

# Configurar Cron para Laravel Scheduler
(crontab -l 2>/dev/null; echo "* * * * * www-data cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Supervisord para filas (opcional)
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/voltcore-worker.conf <<EOF
[program:voltcore-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=3600
EOF

systemctl enable supervisor
systemctl start supervisor
supervisorctl reread 2>/dev/null || true
supervisorctl update 2>/dev/null || true

# Reiniciar serviços
systemctl restart php${PHP_VERSION}-fpm nginx

# Salvar credenciais
CREDS_FILE="/root/voltcore_credentials.txt"
cat > "$CREDS_FILE" <<CREDS
======================================
VoltCore — Credenciais de Acesso
======================================

MySQL Root:
  Usuário: root
  Senha:   ${DB_ROOT_PASS}

MySQL App:
  Banco:   ${DB_NAME}
  Usuário: ${DB_USER}
  Senha:   ${DB_PASS}

Diretório:  ${APP_DIR}
URL:        http://SEU_IP_AQUI

======================================
CREDS

chmod 600 "$CREDS_FILE"

# =============================================================================
# RESUMO FINAL
# =============================================================================
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   VoltCore — Setup Concluído! ✔          ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo "  PHP:       ${PHP_VERSION}"
echo "  Laravel:   11.x"
echo "  MySQL:     8.x (banco: ${DB_NAME})"
echo "  Redis:     instalado"
echo "  Nginx:     configurado"
echo "  Node.js:   $(node --version)"
echo ""
echo -e "${YELLOW}  Credenciais salvas em: ${CREDS_FILE}${NC}"
echo ""
echo "  Próximo passo:"
echo "  1. Acesse o servidor: http://SEU_IP"
echo "  2. Execute as migrations: php artisan migrate --seed"
echo "  3. Clone o repositório e faça o deploy"
echo ""
