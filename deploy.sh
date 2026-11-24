#!/bin/bash
# =============================================================================
# VoltCore — Deploy Script
# Uso: bash deploy.sh  (execute como root, após o setup.sh)
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

[ "$EUID" -ne 0 ] && error "Execute como root: bash deploy.sh"

APP_DIR="/var/www/voltcore"
REPO_URL="https://github.com/carlospjjj-oss/Voltcore-new.git"
CREDS_FILE="/root/voltcore_credentials.txt"
PHP_VERSION="8.3"

# Ler credenciais salvas pelo setup.sh
if [ ! -f "$CREDS_FILE" ]; then
    error "Arquivo $CREDS_FILE não encontrado. Execute o setup.sh primeiro."
fi

DB_PASS=$(grep "Senha:" "$CREDS_FILE" | tail -1 | awk '{print $2}')
DB_NAME="voltcore"
DB_USER="voltcore_user"

header "VoltCore — Deploy"
echo "  App Dir:  $APP_DIR"
echo "  Repo:     $REPO_URL"
echo ""

# =============================================================================
# 1. CLONAR / ATUALIZAR REPOSITÓRIO
# =============================================================================
header "1/5 — Código fonte"

if [ ! -d "$APP_DIR/.git" ]; then
    # Primeira vez: backup do .env e storage, depois clone
    warn "Repositório não inicializado. Copiando código..."

    # Salvar .env do setup.sh se existir
    [ -f "$APP_DIR/.env" ] && cp "$APP_DIR/.env" /tmp/voltcore.env.bak && log ".env salvo em /tmp/voltcore.env.bak"

    # Salvar storage/app/public se existir
    [ -d "$APP_DIR/storage/app/public" ] && cp -r "$APP_DIR/storage/app/public" /tmp/voltcore_storage_bak

    # Clonar em diretório temporário e mover arquivos
    TMP_DIR=$(mktemp -d)
    git clone --depth=1 "$REPO_URL" "$TMP_DIR/repo"

    # Copiar arquivos do repo (exceto .git) por cima do app Laravel existente
    rsync -a --exclude='.git' --exclude='vendor' --exclude='node_modules' \
          "$TMP_DIR/repo/" "$APP_DIR/"

    rm -rf "$TMP_DIR"
    log "Código copiado do repositório"
else
    # Já tem git: fazer pull
    cd "$APP_DIR"
    git fetch origin
    git reset --hard origin/main
    log "Código atualizado (git pull)"
fi

cd "$APP_DIR"

# =============================================================================
# 2. CONFIGURAR .ENV
# =============================================================================
header "2/5 — Configurando ambiente"

if [ -f /tmp/voltcore.env.bak ]; then
    # Restaurar .env que o setup.sh criou (já tem DB, Redis, etc.)
    cp /tmp/voltcore.env.bak "$APP_DIR/.env"
    log ".env restaurado do setup.sh"
elif [ ! -f "$APP_DIR/.env" ]; then
    warn ".env não encontrado. Criando a partir das credenciais..."
    APP_KEY=$(php artisan key:generate --show 2>/dev/null || echo "")
    cat > "$APP_DIR/.env" <<DOTENV
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

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@voltcore.com.br"
MAIL_FROM_NAME="VoltCore"
DOTENV
    php artisan key:generate --force
    log ".env criado com credenciais do setup.sh"
else
    log ".env já existe, mantendo"
fi

# Garantir APP_KEY gerada
grep -q "^APP_KEY=base64:" "$APP_DIR/.env" || php artisan key:generate --force

# =============================================================================
# 3. DEPENDÊNCIAS
# =============================================================================
header "3/5 — Instalando dependências"

# Composer
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3
log "Composer: dependências instaladas"

# NPM + build dos assets
npm ci --prefer-offline 2>/dev/null || npm install
npm run build
log "Assets compilados (Vite + Tailwind)"

# =============================================================================
# 4. MIGRATIONS + CACHE
# =============================================================================
header "4/5 — Banco de dados e cache"

# Migrations
php artisan migrate --seed --force
log "Migrations executadas + seeders"

# Cache de produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
log "Cache de produção gerado"

# Storage link
php artisan storage:link 2>/dev/null || true

# =============================================================================
# 5. PERMISSÕES E SERVIÇOS
# =============================================================================
header "5/5 — Permissões e serviços"

chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"

# Reiniciar PHP-FPM e Nginx
systemctl restart php${PHP_VERSION}-fpm
systemctl reload nginx

# Reiniciar filas
supervisorctl restart voltcore-worker:* 2>/dev/null || true

# Limpar OPCache se possível
php -r "if(function_exists('opcache_reset')) opcache_reset();" 2>/dev/null || true

log "Permissões e serviços ajustados"

# =============================================================================
# RESUMO
# =============================================================================
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   VoltCore — Deploy Concluído! ✔          ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo "  App:   $APP_DIR"
echo ""

# Detectar IP do servidor
SERVER_IP=$(hostname -I | awk '{print $1}')
echo -e "  ${YELLOW}Acesse: http://${SERVER_IP}${NC}"
echo ""
echo "  Login padrão:"
echo "    E-mail: admin@voltcore.local"
echo "    Senha:  voltcore@2025"
echo ""
echo -e "  ${YELLOW}⚠  Troque a senha após o primeiro acesso!${NC}"
echo ""
