#!/bin/bash
# Production Deployment Script for Finder Laravel Application
# Version: 2.0
# Author: Production Team

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration variables
APP_NAME="finder"
APP_PATH="/var/www/html/finder"
BACKUP_PATH="/var/backups/finder"
NGINX_CONFIG="/etc/nginx/sites-available/finder"
PHP_VERSION="8.2"
BRANCH="${1:-main}"
SKIP_TESTS="${2:-false}"

# Print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        print_error "This script should not be run as root"
        exit 1
    fi
}

# Check system requirements
check_requirements() {
    print_status "Checking system requirements..."
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed"
        exit 1
    fi
    
    PHP_CURRENT=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    if ! php -v | grep -q "PHP $PHP_VERSION"; then
        print_warning "PHP version $PHP_CURRENT detected, recommended: $PHP_VERSION"
    fi
    
    # Check Composer
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed"
        exit 1
    fi
    
    # Check Node.js
    if ! command -v node &> /dev/null; then
        print_error "Node.js is not installed"
        exit 1
    fi
    
    # Check Nginx
    if ! command -v nginx &> /dev/null; then
        print_error "Nginx is not installed"
        exit 1
    fi
    
    # Check database connection
    if ! systemctl is-active --quiet mysql && ! systemctl is-active --quiet postgresql; then
        print_error "No database service is running"
        exit 1
    fi
    
    print_success "All requirements met"
}

# Create backup
create_backup() {
    print_status "Creating backup..."
    
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    BACKUP_DIR="$BACKUP_PATH/$TIMESTAMP"
    
    # Create backup directory
    sudo mkdir -p "$BACKUP_DIR"
    
    if [ -d "$APP_PATH" ]; then
        # Backup application files
        sudo cp -r "$APP_PATH" "$BACKUP_DIR/app"
        
        # Backup database
        if systemctl is-active --quiet mysql; then
            DB_NAME=$(grep DB_DATABASE "$APP_PATH/.env" | cut -d '=' -f2)
            DB_USER=$(grep DB_USERNAME "$APP_PATH/.env" | cut -d '=' -f2)
            DB_PASS=$(grep DB_PASSWORD "$APP_PATH/.env" | cut -d '=' -f2)
            
            mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/database.sql"
        elif systemctl is-active --quiet postgresql; then
            DB_NAME=$(grep DB_DATABASE "$APP_PATH/.env" | cut -d '=' -f2)
            pg_dump "$DB_NAME" > "$BACKUP_DIR/database.sql"
        fi
        
        print_success "Backup created at $BACKUP_DIR"
    else
        print_warning "No existing application found, skipping backup"
    fi
}

# Deploy application
deploy_application() {
    print_status "Deploying application..."
    
    # Create application directory if it doesn't exist
    if [ ! -d "$APP_PATH" ]; then
        sudo mkdir -p "$APP_PATH"
        sudo chown -R $USER:www-data "$APP_PATH"
    fi
    
    # Navigate to application directory
    cd "$APP_PATH"
    
    # Pull latest changes or clone repository
    if [ -d ".git" ]; then
        print_status "Pulling latest changes from $BRANCH branch..."
        git fetch origin
        git checkout "$BRANCH"
        git pull origin "$BRANCH"
    else
        print_status "Cloning repository..."
        # Note: Replace with your actual repository URL
        print_warning "Repository URL not configured. Please clone manually or update this script."
        print_status "Expected: git clone <your-repo-url> ."
    fi
    
    print_success "Application code updated"
}

# Install dependencies
install_dependencies() {
    print_status "Installing dependencies..."
    
    cd "$APP_PATH"
    
    # Install Composer dependencies
    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Install NPM dependencies
    print_status "Installing NPM dependencies..."
    npm ci --production --silent
    
    # Build assets
    print_status "Building assets..."
    npm run build
    
    print_success "Dependencies installed and assets built"
}

# Configure environment
configure_environment() {
    print_status "Configuring environment..."
    
    cd "$APP_PATH"
    
    # Copy environment file if it doesn't exist
    if [ ! -f ".env" ]; then
        if [ -f ".env.production.template" ]; then
            cp .env.production.template .env
            print_status "Environment file created from template"
        else
            cp .env.example .env
            print_status "Environment file created from example"
        fi
        
        # Generate application key
        php artisan key:generate --force
        
        print_warning "Please update .env file with production values:"
        print_warning "- Database credentials"
        print_warning "- Mail configuration" 
        print_warning "- Redis password"
        print_warning "- App URL with HTTPS"
        
        read -p "Press Enter after updating .env file..."
    fi
    
    print_success "Environment configured"
}

# Run database migrations
run_migrations() {
    print_status "Running database migrations..."
    
    cd "$APP_PATH"
    
    # Check database connection
    if ! php artisan tinker --execute="DB::connection()->getPdo();" &> /dev/null; then
        print_error "Cannot connect to database. Please check configuration."
        exit 1
    fi
    
    # Run migrations
    php artisan migrate --force
    
    print_success "Database migrations completed"
}

# Optimize application
optimize_application() {
    print_status "Optimizing application..."
    
    cd "$APP_PATH"
    
    # Clear and cache configurations
    php artisan config:clear
    php artisan config:cache
    
    php artisan route:clear
    php artisan route:cache
    
    php artisan view:clear
    php artisan view:cache
    
    # Create storage link
    php artisan storage:link
    
    # Clear application cache
    php artisan cache:clear
    
    print_success "Application optimized"
}

# Set permissions
set_permissions() {
    print_status "Setting file permissions..."
    
    # Set ownership
    sudo chown -R $USER:www-data "$APP_PATH"
    
    # Set directory permissions
    sudo find "$APP_PATH" -type f -exec chmod 644 {} \;
    sudo find "$APP_PATH" -type d -exec chmod 755 {} \;
    
    # Set writable directories
    sudo chmod -R 775 "$APP_PATH/storage"
    sudo chmod -R 775 "$APP_PATH/bootstrap/cache"
    
    # Secure sensitive files
    sudo chmod 600 "$APP_PATH/.env"
    
    print_success "Permissions set correctly"
}

# Run tests
run_tests() {
    if [ "$SKIP_TESTS" = "true" ]; then
        print_warning "Skipping tests as requested"
        return
    fi
    
    print_status "Running tests..."
    
    cd "$APP_PATH"
    
    # Run feature tests
    if ! php artisan test --testsuite=Feature; then
        print_error "Feature tests failed"
        read -p "Continue with deployment? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
    
    # Run security audit
    if ! php artisan security:audit; then
        print_error "Security audit failed"
        read -p "Continue with deployment? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
    
    print_success "All tests passed"
}

# Restart services
restart_services() {
    print_status "Restarting services..."
    
    # Restart PHP-FPM
    sudo systemctl restart php$PHP_VERSION-fpm
    
    # Restart Nginx
    sudo systemctl restart nginx
    
    # Restart Redis if configured
    if systemctl is-active --quiet redis-server; then
        sudo systemctl restart redis-server
    fi
    
    print_success "Services restarted"
}

# Run health checks
run_health_checks() {
    print_status "Running health checks..."
    
    # Check if application responds
    DOMAIN=$(grep APP_URL "$APP_PATH/.env" | cut -d '=' -f2 | sed 's|https\?://||' | sed 's|/||')
    
    if [ -n "$DOMAIN" ]; then
        if curl -f -s -o /dev/null "https://$DOMAIN"; then
            print_success "Application is responding"
        else
            print_error "Application is not responding"
            exit 1
        fi
    else
        print_warning "Could not determine application URL for health check"
    fi
    
    # Check services
    if systemctl is-active --quiet php$PHP_VERSION-fpm; then
        print_success "PHP-FPM is running"
    else
        print_error "PHP-FPM is not running"
    fi
    
    if systemctl is-active --quiet nginx; then
        print_success "Nginx is running"
    else
        print_error "Nginx is not running"
    fi
    
    # Check logs for errors
    ERROR_COUNT=$(tail -100 "/var/log/nginx/finder_error.log" 2>/dev/null | wc -l || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        print_warning "$ERROR_COUNT recent errors in Nginx log"
    fi
    
    print_success "Health checks completed"
}

# Generate deployment report
generate_report() {
    print_status "Generating deployment report..."
    
    REPORT_FILE="/tmp/deployment_report_$(date +%Y%m%d_%H%M%S).txt"
    
    cat > "$REPORT_FILE" << EOF
Finder Laravel Deployment Report
================================
Date: $(date)
Branch: $BRANCH
Deployed by: $USER

Application Status:
- Path: $APP_PATH
- PHP Version: $(php -v | head -n1)
- Composer Version: $(composer --version)
- Node Version: $(node --version)
- NPM Version: $(npm --version)

Service Status:
- PHP-FPM: $(systemctl is-active php$PHP_VERSION-fpm)
- Nginx: $(systemctl is-active nginx)
- Database: $(systemctl is-active mysql || systemctl is-active postgresql)
- Redis: $(systemctl is-active redis-server)

Disk Usage:
$(df -h "$APP_PATH")

Memory Usage:
$(free -h)

Recent Logs (last 5 lines):
$(tail -5 "/var/log/nginx/finder_access.log" 2>/dev/null || echo "No access logs found")

Deployment completed successfully!
EOF
    
    print_success "Report generated: $REPORT_FILE"
    cat "$REPORT_FILE"
}

# Main deployment function
main() {
    echo "========================================"
    echo "  Finder Laravel Production Deployment"
    echo "========================================"
    echo
    
    print_status "Starting deployment process..."
    
    check_root
    check_requirements
    create_backup
    deploy_application
    install_dependencies
    configure_environment
    run_migrations
    optimize_application
    set_permissions
    run_tests
    restart_services
    run_health_checks
    generate_report
    
    echo
    print_success "🚀 Deployment completed successfully!"
    print_status "Application is now running in production mode"
    echo
}

# Script usage
usage() {
    echo "Usage: $0 [branch] [skip_tests]"
    echo "  branch: Git branch to deploy (default: main)"
    echo "  skip_tests: Set to 'true' to skip tests (default: false)"
    echo
    echo "Examples:"
    echo "  $0                    # Deploy main branch with tests"
    echo "  $0 develop           # Deploy develop branch with tests"
    echo "  $0 main true         # Deploy main branch without tests"
}

# Handle script arguments
case "${1:-}" in
    -h|--help)
        usage
        exit 0
        ;;
    *)
        main
        ;;
esac
