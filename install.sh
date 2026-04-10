#!/usr/bin/env bash
#
# Sublicious — Restaurant & Delivery Management SaaS
# Server Installation Script
#
# Usage:
#   chmod +x install.sh
#   sudo ./install.sh
#
# This script will:
#   1. Check system requirements (PHP 8.3+, Composer, Node.js, MySQL/MariaDB)
#   2. Prompt for database credentials
#   3. Prompt for admin account details
#   4. Prompt for app URL and basic config
#   5. Install dependencies (composer + npm)
#   6. Generate app key, run migrations, seed plans + admin
#   7. Build frontend assets
#   8. Set permissions
#   9. Done — ready to serve

set -e

# ─── Colors ──────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

banner() {
    echo ""
    echo -e "${CYAN}╔══════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║                                                  ║${NC}"
    echo -e "${CYAN}║${BOLD}        🍽  SUBLICIOUS INSTALLER  🍽              ${NC}${CYAN}║${NC}"
    echo -e "${CYAN}║     Restaurant & Delivery Management SaaS        ║${NC}"
    echo -e "${CYAN}║                                                  ║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════╝${NC}"
    echo ""
}

info()    { echo -e "${BLUE}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC} $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

prompt() {
    local var_name=$1
    local prompt_text=$2
    local default_value=$3
    local is_secret=$4

    if [ "$default_value" != "" ]; then
        prompt_text="$prompt_text [$default_value]"
    fi

    if [ "$is_secret" = "true" ]; then
        read -sp "  $prompt_text: " input
        echo ""
    else
        read -p "  $prompt_text: " input
    fi

    eval "$var_name=\"${input:-$default_value}\""
}

# ─── Banner ──────────────────────────────────────────────────
banner

# ─── Check if running from project directory ─────────────────
if [ ! -f "artisan" ] || [ ! -f "composer.json" ]; then
    error "Please run this script from the Sublicious project root directory."
fi

# ─── Check Requirements ─────────────────────────────────────
echo -e "${BOLD}Checking system requirements...${NC}"
echo ""

# PHP
if ! command -v php &> /dev/null; then
    error "PHP is not installed. Please install PHP 8.3 or higher."
fi
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")
if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 3 ]); then
    error "PHP 8.3+ required, found $PHP_VERSION"
fi
success "PHP $PHP_VERSION"

# Required PHP extensions
for ext in pdo mbstring openssl tokenizer xml ctype json bcmath curl; do
    if php -m 2>/dev/null | grep -qi "^$ext$"; then
        success "PHP extension: $ext"
    else
        warn "PHP extension '$ext' may be missing — check with: php -m | grep $ext"
    fi
done

# Composer
if ! command -v composer &> /dev/null; then
    error "Composer is not installed. Install it from https://getcomposer.org"
fi
success "Composer $(composer --version 2>/dev/null | head -1 | grep -oP '[\d.]+')"

# Node.js
if ! command -v node &> /dev/null; then
    error "Node.js is not installed. Install Node.js 18+ from https://nodejs.org"
fi
NODE_VERSION=$(node -v)
success "Node.js $NODE_VERSION"

# npm
if ! command -v npm &> /dev/null; then
    error "npm is not installed."
fi
success "npm $(npm -v)"

# MySQL/MariaDB (optional — could use SQLite)
if command -v mysql &> /dev/null; then
    success "MySQL/MariaDB client found"
    HAS_MYSQL=true
else
    warn "MySQL client not found — you can still use SQLite for development"
    HAS_MYSQL=false
fi

echo ""
echo -e "${BOLD}All requirements met!${NC}"
echo ""

# ─── Database Configuration ──────────────────────────────────
echo -e "${BOLD}━━━ Database Configuration ━━━${NC}"
echo ""
echo "  Select database driver:"
echo "    1) MySQL / MariaDB (recommended for production)"
echo "    2) SQLite (simple, no setup needed)"
echo ""
read -p "  Choose [1/2] (default: 1): " DB_CHOICE
DB_CHOICE=${DB_CHOICE:-1}

if [ "$DB_CHOICE" = "2" ]; then
    DB_CONNECTION="sqlite"
    DB_DATABASE=""
    DB_HOST=""
    DB_PORT=""
    DB_USERNAME=""
    DB_PASSWORD=""
    info "Using SQLite — database will be at database/database.sqlite"
    touch database/database.sqlite 2>/dev/null || true
else
    DB_CONNECTION="mysql"
    prompt DB_HOST "Database host" "127.0.0.1"
    prompt DB_PORT "Database port" "3306"
    prompt DB_DATABASE "Database name" "sublicious"
    prompt DB_USERNAME "Database username" "root"
    prompt DB_PASSWORD "Database password" "" "true"

    # Test connection
    info "Testing database connection..."
    if command -v mysql &> /dev/null; then
        if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -e "SELECT 1;" &>/dev/null; then
            success "Database connection successful"
            # Create database if not exists
            mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
            success "Database '$DB_DATABASE' ready"
        else
            warn "Could not connect to database. Make sure it's running and credentials are correct."
            read -p "  Continue anyway? [y/N]: " CONTINUE
            [ "$CONTINUE" != "y" ] && [ "$CONTINUE" != "Y" ] && exit 1
        fi
    else
        warn "MySQL client not available to test. Continuing..."
    fi
fi
echo ""

# ─── Application Configuration ───────────────────────────────
echo -e "${BOLD}━━━ Application Configuration ━━━${NC}"
echo ""
prompt APP_URL "Application URL" "http://localhost"
prompt APP_NAME "Application name" "Sublicious"
prompt APP_TIMEZONE "Timezone" "Asia/Colombo"
echo ""

# ─── Super Admin Configuration ───────────────────────────────
echo -e "${BOLD}━━━ Super Admin Account ━━━${NC}"
echo ""
prompt ADMIN_NAME "Admin full name" "Super Admin"
prompt ADMIN_EMAIL "Admin email" "admin@sublicious.app"
prompt ADMIN_PASSWORD "Admin password (min 8 chars)" "" "true"

while [ ${#ADMIN_PASSWORD} -lt 8 ]; do
    warn "Password must be at least 8 characters."
    prompt ADMIN_PASSWORD "Admin password (min 8 chars)" "" "true"
done
echo ""

# ─── SMS Configuration (Optional) ────────────────────────────
echo -e "${BOLD}━━━ SMS Configuration (SMSlenz) — Optional ━━━${NC}"
echo ""
read -p "  Configure SMS now? [y/N]: " CONFIGURE_SMS
CONFIGURE_SMS=${CONFIGURE_SMS:-N}

SMS_USER_ID=""
SMS_API_KEY=""
SMS_SENDER_ID=""

if [ "$CONFIGURE_SMS" = "y" ] || [ "$CONFIGURE_SMS" = "Y" ]; then
    prompt SMS_USER_ID "SMSlenz User ID" ""
    prompt SMS_API_KEY "SMSlenz API Key" ""
    prompt SMS_SENDER_ID "Sender ID" "SMSlenzDEMO"
fi
echo ""

# ─── Mail Configuration (Optional) ───────────────────────────
echo -e "${BOLD}━━━ Mail Configuration — Optional ━━━${NC}"
echo ""
read -p "  Configure SMTP mail now? [y/N]: " CONFIGURE_MAIL
CONFIGURE_MAIL=${CONFIGURE_MAIL:-N}

MAIL_HOST="127.0.0.1"
MAIL_PORT="2525"
MAIL_USERNAME=""
MAIL_PASSWORD=""
MAIL_FROM="noreply@sublicious.app"
MAIL_MAILER="log"

if [ "$CONFIGURE_MAIL" = "y" ] || [ "$CONFIGURE_MAIL" = "Y" ]; then
    MAIL_MAILER="smtp"
    prompt MAIL_HOST "SMTP host" "smtp.gmail.com"
    prompt MAIL_PORT "SMTP port" "587"
    prompt MAIL_USERNAME "SMTP username" ""
    prompt MAIL_PASSWORD "SMTP password" "" "true"
    prompt MAIL_FROM "From email address" "noreply@sublicious.app"
fi
echo ""

# ─── Confirmation ────────────────────────────────────────────
echo -e "${BOLD}━━━ Configuration Summary ━━━${NC}"
echo ""
echo -e "  App Name:     ${CYAN}$APP_NAME${NC}"
echo -e "  App URL:      ${CYAN}$APP_URL${NC}"
echo -e "  Timezone:     ${CYAN}$APP_TIMEZONE${NC}"
echo -e "  Database:     ${CYAN}$DB_CONNECTION${NC}"
[ "$DB_CONNECTION" = "mysql" ] && echo -e "  DB Name:      ${CYAN}$DB_DATABASE${NC}"
[ "$DB_CONNECTION" = "mysql" ] && echo -e "  DB Host:      ${CYAN}$DB_HOST:$DB_PORT${NC}"
echo -e "  Admin Email:  ${CYAN}$ADMIN_EMAIL${NC}"
[ -n "$SMS_USER_ID" ] && echo -e "  SMS:          ${CYAN}SMSlenz (User ID: $SMS_USER_ID)${NC}"
[ "$MAIL_MAILER" = "smtp" ] && echo -e "  Mail:         ${CYAN}SMTP ($MAIL_HOST:$MAIL_PORT)${NC}"
echo ""
read -p "  Proceed with installation? [Y/n]: " CONFIRM
CONFIRM=${CONFIRM:-Y}
[ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ] && { echo "Installation cancelled."; exit 0; }

echo ""
echo -e "${BOLD}━━━ Installing... ━━━${NC}"
echo ""

# ─── Step 1: Environment File ────────────────────────────────
info "Creating .env file..."
cp .env.example .env 2>/dev/null || cp .env .env.bak

cat > .env << ENVEOF
APP_NAME="$APP_NAME"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=$APP_URL
APP_TIMEZONE=$APP_TIMEZONE

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=$DB_CONNECTION
$([ "$DB_CONNECTION" = "mysql" ] && cat << MYSQLEOF
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD
MYSQLEOF
)

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=$MAIL_MAILER
MAIL_SCHEME=null
MAIL_HOST=$MAIL_HOST
MAIL_PORT=$MAIL_PORT
MAIL_USERNAME=$MAIL_USERNAME
MAIL_PASSWORD=$MAIL_PASSWORD
MAIL_FROM_ADDRESS="$MAIL_FROM"
MAIL_FROM_NAME="\${APP_NAME}"

REVERB_APP_ID=sublicious-local
REVERB_APP_KEY=sublicious-key
REVERB_APP_SECRET=sublicious-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_APP_NAME="\${APP_NAME}"
VITE_REVERB_APP_KEY="\${REVERB_APP_KEY}"
VITE_REVERB_HOST="\${REVERB_HOST}"
VITE_REVERB_PORT="\${REVERB_PORT}"
VITE_REVERB_SCHEME="\${REVERB_SCHEME}"

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=

SUPER_ADMIN_EMAIL=$ADMIN_EMAIL
SUPER_ADMIN_PASSWORD=$ADMIN_PASSWORD
SUPER_ADMIN_NAME="$ADMIN_NAME"
ENVEOF

success ".env file created"

# ─── Step 2: Install PHP Dependencies ────────────────────────
info "Installing Composer dependencies (this may take a minute)..."
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3
success "Composer dependencies installed"

# ─── Step 3: Generate App Key ─────────────────────────────────
info "Generating application key..."
php artisan key:generate --force --no-interaction
success "Application key generated"

# ─── Step 4: Install Node Dependencies & Build ────────────────
info "Installing Node.js dependencies..."
npm ci --silent 2>&1 | tail -2
success "Node dependencies installed"

info "Building frontend assets..."
npm run build 2>&1 | tail -3
success "Frontend assets built"

# ─── Step 5: SQLite Setup (if applicable) ─────────────────────
if [ "$DB_CONNECTION" = "sqlite" ]; then
    info "Creating SQLite database..."
    touch database/database.sqlite
    success "SQLite database created"
fi

# ─── Step 6: Run Migrations ──────────────────────────────────
info "Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 | tail -5
success "Database migrations complete"

# ─── Step 7: Seed Database ───────────────────────────────────
info "Seeding plans and admin account..."
php artisan db:seed --force --no-interaction 2>&1 | tail -5
success "Database seeded — plans created and admin account ready"

# ─── Step 8: Storage Link ────────────────────────────────────
info "Creating storage symlink..."
php artisan storage:link --force --no-interaction 2>/dev/null || true
success "Storage linked"

# ─── Step 9: Cache Config ────────────────────────────────────
info "Caching configuration for performance..."
php artisan config:cache --no-interaction 2>/dev/null
php artisan route:cache --no-interaction 2>/dev/null
php artisan view:cache --no-interaction 2>/dev/null
success "Configuration cached"

# ─── Step 10: Store SMS config as platform settings ──────────
if [ -n "$SMS_USER_ID" ]; then
    info "Saving SMS configuration..."
    php artisan tinker --execute="
        \App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_user_id'],
            ['value' => '$SMS_USER_ID', 'group' => 'integrations']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_api_key'],
            ['value' => '$SMS_API_KEY', 'group' => 'integrations']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_sender_id'],
            ['value' => '$SMS_SENDER_ID', 'group' => 'integrations']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['business_id' => null, 'key' => 'sms_base_url'],
            ['value' => 'https://smslenz.lk/api', 'group' => 'integrations']
        );
    " 2>/dev/null
    success "SMS configuration saved"
fi

# ─── Step 11: Set Permissions ─────────────────────────────────
info "Setting file permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || {
    warn "Could not set www-data ownership — you may need to do this manually"
}
success "Permissions set"

# ─── Done ─────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                  ║${NC}"
echo -e "${GREEN}║   INSTALLATION COMPLETE!                         ║${NC}"
echo -e "${GREEN}║                                                  ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BOLD}Application URL:${NC}  $APP_URL"
echo -e "  ${BOLD}Admin Login:${NC}      $APP_URL/login"
echo -e "  ${BOLD}Admin Email:${NC}      $ADMIN_EMAIL"
echo -e "  ${BOLD}Admin Password:${NC}   (the one you entered)"
echo ""
echo -e "  ${BOLD}Quick Start:${NC}"
echo -e "    1. Point your web server (Nginx/Apache) document root to:"
echo -e "       ${CYAN}$(pwd)/public${NC}"
echo -e ""
echo -e "    2. For local testing, run:"
echo -e "       ${CYAN}php artisan serve${NC}"
echo -e ""
echo -e "    3. For background queue processing:"
echo -e "       ${CYAN}php artisan queue:work${NC}"
echo -e ""
echo -e "    4. For WebSocket (real-time updates):"
echo -e "       ${CYAN}php artisan reverb:start${NC}"
echo ""
echo -e "  ${BOLD}Nginx Config Example:${NC}"
echo -e "    server {"
echo -e "        listen 80;"
echo -e "        server_name yourdomain.com;"
echo -e "        root $(pwd)/public;"
echo -e "        index index.php;"
echo -e ""
echo -e "        location / {"
echo -e "            try_files \$uri \$uri/ /index.php?\$query_string;"
echo -e "        }"
echo -e ""
echo -e "        location ~ \\.php\$ {"
echo -e "            fastcgi_pass unix:/run/php/php-fpm.sock;"
echo -e "            fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;"
echo -e "            include fastcgi_params;"
echo -e "        }"
echo -e "    }"
echo ""
echo -e "  ${YELLOW}Need help? Visit the project README or contact support.${NC}"
echo ""
