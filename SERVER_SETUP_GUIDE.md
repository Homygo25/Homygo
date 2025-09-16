# Production Server Setup Guide for Finder Laravel

## 🖥️ Server Requirements

### Minimum Requirements

- **OS**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 20GB minimum, 50GB recommended
- **CPU**: 2 cores minimum
- **Network**: Static IP address

### Software Stack

- **Web Server**: Nginx 1.18+
- **PHP**: 8.2+
- **Database**: MySQL 8.0+ / PostgreSQL 13+
- **Cache**: Redis 6.0+
- **SSL**: Let's Encrypt / Custom SSL Certificate

---

## 🔧 Server Initial Setup

### Step 1: System Updates and Security

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common

# Configure firewall
sudo ufw enable
sudo ufw allow 22    # SSH
sudo ufw allow 80    # HTTP
sudo ufw allow 443   # HTTPS
sudo ufw status

# Create application user
sudo adduser finder
sudo usermod -aG sudo finder
sudo usermod -aG www-data finder
```

### Step 2: Install PHP 8.2

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and required extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-pgsql php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-bcmath php8.2-redis php8.2-intl \
    php8.2-soap php8.2-xmlrpc php8.2-xsl

# Configure PHP for production
sudo nano /etc/php/8.2/fpm/php.ini
```

### Step 3: PHP Production Configuration

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
; Basic Settings
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
post_max_size = 32M
upload_max_filesize = 32M

; Security Settings
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Session Security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1

; OPcache Configuration
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

### Step 4: Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### Step 5: Install Node.js and NPM

```bash
# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version
```

---

## 🌐 Nginx Installation and Configuration

### Step 1: Install Nginx

```bash
# Install Nginx
sudo apt install nginx -y

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
sudo systemctl status nginx
```

### Step 2: Create Nginx Configuration for Finder

Create `/etc/nginx/sites-available/finder`:

```nginx
# Finder Laravel Application - Production Configuration
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    # Document root
    root /var/www/html/finder/public;
    index index.php index.html index.htm;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;" always;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=100r/m;
    
    # Security: Block common attack patterns
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ \.(htaccess|htpasswd|env|git) {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # PHP files handling
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }
    
    # Laravel Application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Rate limiting for authentication endpoints
    location ~ ^/(login|register|password) {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # API rate limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|webp|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        log_not_found off;
    }
    
    # Asset versioning support
    location ~* \.(css|js)$ {
        expires 1y;
        add_header Cache-Control "public";
        access_log off;
        
        # Handle Laravel Mix versioning
        try_files $uri @fallback;
    }
    
    location @fallback {
        rewrite ^/build/(.+)$ /build/manifest.json last;
    }
    
    # Error pages
    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    
    # Logs
    access_log /var/log/nginx/finder_access.log;
    error_log /var/log/nginx/finder_error.log;
    
    # Client settings
    client_max_body_size 32M;
    client_body_timeout 60s;
    client_header_timeout 60s;
}
```

### Step 3: Enable Site and Test Configuration

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/finder /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## 🔒 SSL Certificate Setup with Let's Encrypt

### Step 1: Install Certbot

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y
```

### Step 2: Obtain SSL Certificate

```bash
# Get SSL certificate (replace with your domain)
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

### Step 3: Setup Auto-renewal

```bash
# Add cron job for automatic renewal
sudo crontab -e

# Add this line to crontab:
0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 📁 Application Deployment Directory

### Step 1: Create Application Directory

```bash
# Create directory structure
sudo mkdir -p /var/www/html/finder
sudo chown -R finder:www-data /var/www/html/finder

# Set proper permissions
sudo chmod -R 755 /var/www/html/finder
```

### Step 2: Deploy Application Files

```bash
# Navigate to application directory
cd /var/www/html/finder

# Clone repository (or upload files)
git clone https://github.com/your-repo/finder-laravel.git .

# Or if uploading files, ensure proper ownership
sudo chown -R finder:www-data /var/www/html/finder
```

### Step 3: Set Correct Permissions

```bash
# Application permissions
sudo find /var/www/html/finder -type f -exec chmod 644 {} \;
sudo find /var/www/html/finder -type d -exec chmod 755 {} \;

# Writable directories
sudo chmod -R 775 /var/www/html/finder/storage
sudo chmod -R 775 /var/www/html/finder/bootstrap/cache

# Sensitive files
sudo chmod 600 /var/www/html/finder/.env
sudo chown finder:www-data /var/www/html/finder/.env
```

---

## ⚡ Redis Installation and Configuration

### Step 1: Install Redis

```bash
# Install Redis
sudo apt install redis-server -y

# Configure Redis for production
sudo nano /etc/redis/redis.conf
```

### Step 2: Redis Production Configuration

Edit `/etc/redis/redis.conf`:

```
# Network configuration
bind 127.0.0.1 ::1
port 6379
protected-mode yes

# Security
requirepass YOUR_REDIS_PASSWORD

# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
```

### Step 3: Start and Enable Redis

```bash
# Start and enable Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test Redis connection
redis-cli ping
```

---

## 🚀 Application Deployment Process

### Step 1: Install Dependencies

```bash
# Navigate to application directory
cd /var/www/html/finder

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build assets
npm ci --production
npm run build
```

### Step 2: Environment Configuration

```bash
# Copy production environment file
cp .env.production.template .env

# Generate application key
php artisan key:generate

# Update .env with production values:
# - Database credentials
# - Redis password
# - Mail configuration
# - App URL with HTTPS
```

### Step 3: Database Setup

```bash
# Run database migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link
```

### Step 4: Final Permissions

```bash
# Set final permissions
sudo chown -R finder:www-data /var/www/html/finder
sudo chmod -R 755 /var/www/html/finder
sudo chmod -R 775 /var/www/html/finder/storage
sudo chmod -R 775 /var/www/html/finder/bootstrap/cache
```

---

## 🔍 Production Health Checks

### Step 1: Test Application

```bash
# Test application response
curl -I https://your-domain.com

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check Nginx status
sudo systemctl status nginx

# Test database connection
php artisan tinker
# DB::connection()->getPdo();
```

### Step 2: Run Security Audit

```bash
# Run comprehensive security audit
php artisan security:audit

# Check for any critical issues
```

### Step 3: Performance Testing

```bash
# Test page load times
curl -w "@curl-format.txt" -o /dev/null -s https://your-domain.com

# Monitor server resources
htop
free -h
df -h
```

---

## 📊 Monitoring and Logging

### Log Locations

- **Nginx Access**: `/var/log/nginx/finder_access.log`
- **Nginx Error**: `/var/log/nginx/finder_error.log`
- **PHP Errors**: `/var/log/php_errors.log`
- **Laravel Logs**: `/var/www/html/finder/storage/logs/laravel.log`
- **Redis Logs**: `/var/log/redis/redis-server.log`

### Setup Log Rotation

Create `/etc/logrotate.d/finder`:

```
/var/log/nginx/finder_*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data adm
    postrotate
        systemctl reload nginx
    endscript
}

/var/www/html/finder/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 finder www-data
}
```

---

## ✅ Server Setup Verification Checklist

After completing server setup:

- [ ] Server updated and secured
- [ ] PHP 8.2 installed and configured
- [ ] Nginx installed and configured
- [ ] SSL certificate installed (Let's Encrypt)
- [ ] Database server configured
- [ ] Redis installed and configured
- [ ] Application deployed
- [ ] Dependencies installed
- [ ] Environment configured
- [ ] Database migrated
- [ ] Permissions set correctly
- [ ] Security headers active
- [ ] Rate limiting configured
- [ ] Logs configured and rotated
- [ ] Health checks passing
- [ ] Security audit passed

---

**Next Step**: Deploy using automated scripts  
**Estimated Time**: 4-6 hours for complete server setup
