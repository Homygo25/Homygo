#!/bin/bash
# Finder Laravel Application - Production Deployment Script
# 
# This script handles the deployment of the Finder Laravel application to production.
# It includes security checks, database migrations, cache optimization, and more.

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/html/finder"
BACKUP_DIR="/var/backups/finder"
MAINTENANCE_MODE=true
PHP_BINARY="/usr/bin/php8.2"  # Adjust PHP path as needed

# Logging
LOG_FILE="/var/log/finder-deployment.log"

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a $LOG_FILE
}

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
    log "INFO: $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
    log "SUCCESS: $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
    log "WARNING: $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
    log "ERROR: $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Pre-deployment checks
print_status "Starting Finder Laravel deployment process..."

# Check if running as proper user
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. Consider using a dedicated deployment user."
fi

# Check required commands
REQUIRED_COMMANDS=("$PHP_BINARY" "composer" "npm" "mysql" "redis-cli")
for cmd in "${REQUIRED_COMMANDS[@]}"; do
    if ! command_exists "$cmd"; then
        print_error "Required command '$cmd' not found. Please install it first."
        exit 1
    fi
done

# Check if app directory exists
if [ ! -d "$APP_DIR" ]; then
    print_error "Application directory $APP_DIR does not exist."
    exit 1
fi

# Change to application directory
cd "$APP_DIR"

# Backup before deployment
print_status "Creating backup..."
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/finder-backup-$(date +%Y%m%d-%H%M%S).tar.gz"
tar -czf "$BACKUP_FILE" --exclude='node_modules' --exclude='storage/logs/*' .
print_success "Backup created: $BACKUP_FILE"

# Put application in maintenance mode
if [ "$MAINTENANCE_MODE" = true ]; then
    print_status "Putting application in maintenance mode..."
    $PHP_BINARY artisan down --message="Deploying updates. We'll be back shortly!"
fi

# Git pull (if using git deployment)
if [ -d ".git" ]; then
    print_status "Pulling latest changes from repository..."
    git pull origin main
fi

# Install/update Composer dependencies
print_status "Installing/updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install/update NPM dependencies
print_status "Installing/updating NPM dependencies..."
npm ci --production

# Build assets
print_status "Building production assets..."
npm run build

# Clear and optimize caches
print_status "Clearing and optimizing caches..."
$PHP_BINARY artisan config:clear
$PHP_BINARY artisan route:clear
$PHP_BINARY artisan view:clear
$PHP_BINARY artisan cache:clear

# Cache configuration for production
print_status "Caching configuration for production..."
$PHP_BINARY artisan config:cache
$PHP_BINARY artisan route:cache
$PHP_BINARY artisan view:cache

# Run database migrations
print_status "Running database migrations..."
$PHP_BINARY artisan migrate --force

# Seed database if needed (uncomment if you have production seeders)
# print_status "Seeding database..."
# $PHP_BINARY artisan db:seed --force

# Set proper permissions
print_status "Setting proper file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chmod -R 755 public

# Restart queue workers (if using queues)
print_status "Restarting queue workers..."
$PHP_BINARY artisan queue:restart

# Clear opcache if available
if command_exists "php-opcache-reset"; then
    print_status "Clearing OPcache..."
    php-opcache-reset
fi

# Restart PHP-FPM
if command_exists "systemctl"; then
    print_status "Restarting PHP-FPM..."
    systemctl restart php8.2-fpm
fi

# Take application out of maintenance mode
if [ "$MAINTENANCE_MODE" = true ]; then
    print_status "Taking application out of maintenance mode..."
    $PHP_BINARY artisan up
fi

# Run post-deployment tests
print_status "Running post-deployment tests..."
$PHP_BINARY artisan route:list > /dev/null && print_success "Routes loaded successfully"

# Check if application is responding
if command_exists "curl"; then
    if curl -f -s -o /dev/null "http://localhost"; then
        print_success "Application is responding to HTTP requests"
    else
        print_warning "Application may not be responding properly"
    fi
fi

# Final checks
print_status "Running final health checks..."

# Check storage permissions
if [ -w "storage/logs" ]; then
    print_success "Storage directory is writable"
else
    print_error "Storage directory is not writable"
fi

# Check if .env exists
if [ -f ".env" ]; then
    print_success ".env file exists"
else
    print_error ".env file not found"
fi

# Check database connection
if $PHP_BINARY artisan migrate:status > /dev/null 2>&1; then
    print_success "Database connection successful"
else
    print_error "Database connection failed"
fi

print_success "Deployment completed successfully!"
print_status "Deployment log: $LOG_FILE"
print_status "Backup location: $BACKUP_FILE"

# Send notification (optional - configure with your notification system)
# curl -X POST "https://hooks.slack.com/your-webhook" \
#      -H 'Content-type: application/json' \
#      --data '{"text":"Finder Laravel application deployed successfully"}'

log "Deployment completed at $(date)"
