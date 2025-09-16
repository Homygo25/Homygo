# Production Database Setup Guide for Finder Laravel

## 🗄️ Database Options

### Option 1: MySQL 8.0+ (Recommended)

### Option 2: PostgreSQL 13+ (Alternative)

---

## 🔧 MySQL 8.0+ Setup (Recommended)

### Step 1: Install MySQL Server

#### Ubuntu/Debian

```bash
# Update package list
sudo apt update

# Install MySQL server
sudo apt install mysql-server

# Secure MySQL installation
sudo mysql_secure_installation
```

#### CentOS/RHEL

```bash
# Install MySQL repository
sudo dnf install mysql-server

# Start and enable MySQL
sudo systemctl start mysqld
sudo systemctl enable mysqld

# Secure installation
sudo mysql_secure_installation
```

### Step 2: Create Production Database and User

```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE finder_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user with minimal permissions
CREATE USER 'finder_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER 
ON finder_production.* TO 'finder_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

### Step 3: Configure MySQL for Production

#### Edit MySQL Configuration `/etc/mysql/mysql.conf.d/mysqld.cnf`

```ini
[mysqld]
# Basic Settings
user = mysql
pid-file = /var/run/mysqld/mysqld.pid
socket = /var/run/mysqld/mysqld.sock
port = 3306
basedir = /usr
datadir = /var/lib/mysql
tmpdir = /tmp
lc-messages-dir = /usr/share/mysql

# Security Settings
bind-address = 127.0.0.1  # Only allow local connections
skip-networking = 0
skip-name-resolve

# Performance Tuning
innodb_buffer_pool_size = 256M  # Adjust based on available RAM
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 1
innodb_file_per_table = 1

# Character Set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# SSL Configuration (Recommended)
ssl-ca = /etc/mysql/ca.pem
ssl-cert = /etc/mysql/server-cert.pem
ssl-key = /etc/mysql/server-key.pem

# Logging
log-error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Backup Settings
expire_logs_days = 7
max_binlog_size = 100M
```

### Step 4: Restart MySQL

```bash
sudo systemctl restart mysql
```

---

## 🐘 PostgreSQL 13+ Setup (Alternative)

### Step 1: Install PostgreSQL

#### Ubuntu/Debian

```bash
# Install PostgreSQL
sudo apt update
sudo apt install postgresql postgresql-contrib

# Start and enable PostgreSQL
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### Step 2: Create Database and User

```bash
# Switch to postgres user
sudo -u postgres psql

-- Create database
CREATE DATABASE finder_production;

-- Create user
CREATE USER finder_user WITH ENCRYPTED PASSWORD 'STRONG_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE finder_production TO finder_user;

-- Exit PostgreSQL
\q
```

### Step 3: Configure PostgreSQL

#### Edit `/etc/postgresql/13/main/postgresql.conf`

```ini
# Connection Settings
listen_addresses = 'localhost'
port = 5432
max_connections = 100

# Memory Settings
shared_buffers = 128MB
effective_cache_size = 256MB
work_mem = 4MB
maintenance_work_mem = 64MB

# Logging
log_destination = 'stderr'
logging_collector = on
log_directory = 'log'
log_filename = 'postgresql-%Y-%m-%d_%H%M%S.log'
log_statement = 'error'
log_min_duration_statement = 1000

# SSL Settings
ssl = on
ssl_cert_file = 'server.crt'
ssl_key_file = 'server.key'
```

#### Edit `/etc/postgresql/13/main/pg_hba.conf`

```
# Local connections
local   all             postgres                                peer
local   all             all                                     md5

# IPv4 local connections:
host    all             all             127.0.0.1/32            md5
```

### Step 4: Restart PostgreSQL

```bash
sudo systemctl restart postgresql
```

---

## 🔧 Application Configuration

### Update Laravel .env for Production Database

#### For MySQL

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finder_production
DB_USERNAME=finder_user
DB_PASSWORD=YOUR_STRONG_PASSWORD
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# SSL Configuration (if enabled)
MYSQL_ATTR_SSL_CA=/path/to/ca.pem
MYSQL_ATTR_SSL_CERT=/path/to/client-cert.pem
MYSQL_ATTR_SSL_KEY=/path/to/client-key.pem
MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=true
```

#### For PostgreSQL

```env
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=finder_production
DB_USERNAME=finder_user
DB_PASSWORD=YOUR_STRONG_PASSWORD
DB_CHARSET=utf8
```

---

## 🚀 Database Migration and Setup

### Step 1: Test Database Connection

```bash
# Navigate to application directory
cd /var/www/html/finder

# Test database connection
php artisan tinker
# In tinker: DB::connection()->getPdo();
# Should return PDO object without errors
```

### Step 2: Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# Verify tables were created
php artisan tinker
# In tinker: Schema::hasTable('users');
# Should return true
```

### Step 3: Seed Initial Data (if needed)

```bash
# Run database seeders
php artisan db:seed --force

# Or specific seeders
php artisan db:seed --class=DatabaseSeeder --force
```

---

## 🔐 Database Security Best Practices

### 1. **Strong Authentication**

- Use strong, unique passwords (minimum 16 characters)
- Consider using MySQL/PostgreSQL authentication plugins
- Regularly rotate database passwords

### 2. **Network Security**

- Bind database to localhost only (`bind-address = 127.0.0.1`)
- Use SSL/TLS for database connections
- Configure firewall to block external database access

### 3. **User Permissions**

- Grant only necessary permissions to application user
- Never use root/postgres user for application connections
- Create separate users for different environments

### 4. **Monitoring & Logging**

- Enable slow query logging
- Monitor failed connection attempts
- Set up log rotation
- Regular security audits

### 5. **Backup Strategy**

- Automated daily backups
- Test backup restoration regularly
- Store backups in secure, separate location
- Encrypt backup files

---

## 📊 Database Performance Optimization

### MySQL Optimization

```sql
-- Check current settings
SHOW VARIABLES LIKE 'innodb_buffer_pool_size';

-- Optimize for Finder application
SET GLOBAL innodb_buffer_pool_size = 268435456; -- 256MB

-- Add useful indexes
CREATE INDEX idx_properties_user_id ON properties(user_id);
CREATE INDEX idx_properties_created_at ON properties(created_at);
CREATE INDEX idx_properties_price ON properties(price);
CREATE INDEX idx_properties_location ON properties(city, state);
```

### PostgreSQL Optimization

```sql
-- Check current settings
SHOW shared_buffers;

-- Useful indexes for Finder
CREATE INDEX CONCURRENTLY idx_properties_user_id ON properties(user_id);
CREATE INDEX CONCURRENTLY idx_properties_created_at ON properties(created_at);
CREATE INDEX CONCURRENTLY idx_properties_price ON properties(price);
CREATE INDEX CONCURRENTLY idx_properties_location ON properties(city, state);

-- Analyze tables
ANALYZE properties;
ANALYZE users;
```

---

## 🔍 Database Health Monitoring

### Automated Health Checks

Our `health-check.sh` script includes database monitoring:

```bash
# Check database connectivity
php artisan migrate:status

# Monitor database performance
# - Connection count
# - Slow queries
# - Database size
# - Index usage
```

### Key Metrics to Monitor

- **Connection count**: Should stay under max_connections
- **Query response time**: < 100ms for simple queries
- **Database size growth**: Plan for scaling
- **Index efficiency**: Monitor query execution plans
- **Backup completion**: Verify daily backup success

---

## ⚠️ Troubleshooting Common Issues

### Connection Issues

```bash
# Test database connectivity
telnet localhost 3306  # MySQL
telnet localhost 5432  # PostgreSQL

# Check MySQL process
sudo systemctl status mysql

# Check PostgreSQL process
sudo systemctl status postgresql

# View error logs
sudo tail -f /var/log/mysql/error.log
sudo tail -f /var/log/postgresql/postgresql-*.log
```

### Permission Issues

```sql
-- MySQL: Check user permissions
SHOW GRANTS FOR 'finder_user'@'localhost';

-- PostgreSQL: Check user permissions
\du finder_user
```

### Performance Issues

```sql
-- MySQL: Check slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;

-- PostgreSQL: Enable query logging
SET log_statement = 'all';
```

---

## ✅ Verification Checklist

After completing database setup:

- [ ] Database server installed and running
- [ ] Production database created
- [ ] Application user created with minimal permissions
- [ ] SSL/TLS configured (if required)
- [ ] Laravel .env updated with production credentials
- [ ] Database connection tested successfully
- [ ] Migrations run successfully
- [ ] Initial data seeded (if applicable)
- [ ] Performance indexes created
- [ ] Backup strategy configured
- [ ] Monitoring and logging enabled
- [ ] Security audit passed

---

**Next Step**: Configure Production Server (Nginx + SSL)
**Estimated Time**: 4-6 hours for complete database setup
