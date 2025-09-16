#!/bin/bash
# Finder Laravel Application - Backup Script
#
# This script creates comprehensive backups of the Finder Laravel application
# including database, files, and configuration.

set -e

# Configuration
APP_DIR="/var/www/html/finder"
BACKUP_BASE_DIR="/var/backups/finder"
RETENTION_DAYS=30
DB_NAME="finder_production"
DB_USER="finder_user"
DB_PASSWORD=""  # Set this or use .my.cnf
BACKUP_ENCRYPTION_KEY=""  # Set for encrypted backups

# S3 Configuration (optional)
S3_BUCKET="finder-backups"
S3_REGION="us-east-1"
AWS_PROFILE="default"

# Notification settings
SLACK_WEBHOOK=""
ALERT_EMAIL="admin@your-domain.com"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Logging
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
LOG_FILE="$BACKUP_BASE_DIR/logs/backup_$TIMESTAMP.log"

# Create necessary directories
mkdir -p "$BACKUP_BASE_DIR"/{daily,weekly,monthly,logs}

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
    log "INFO: $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
    log "SUCCESS: $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
    log "ERROR: $1"
}

# Function to send notifications
send_notification() {
    local message="$1"
    local status="$2"  # SUCCESS or ERROR
    
    local emoji="✅"
    [ "$status" = "ERROR" ] && emoji="❌"
    
    # Email notification
    if command -v mail >/dev/null 2>&1 && [ -n "$ALERT_EMAIL" ]; then
        echo "$message" | mail -s "Finder Backup $status" "$ALERT_EMAIL"
    fi
    
    # Slack notification
    if [ -n "$SLACK_WEBHOOK" ] && command -v curl >/dev/null 2>&1; then
        curl -X POST "$SLACK_WEBHOOK" \
             -H 'Content-type: application/json' \
             --data "{\"text\":\"$emoji Finder Backup $status: $message\"}" \
             --silent --output /dev/null
    fi
}

# Function to check available disk space
check_disk_space() {
    local required_space_gb="$1"
    local available_space_kb=$(df "$BACKUP_BASE_DIR" | tail -1 | awk '{print $4}')
    local available_space_gb=$((available_space_kb / 1024 / 1024))
    
    if [ "$available_space_gb" -lt "$required_space_gb" ]; then
        print_error "Insufficient disk space. Required: ${required_space_gb}GB, Available: ${available_space_gb}GB"
        return 1
    fi
    
    print_status "Disk space check passed. Available: ${available_space_gb}GB"
    return 0
}

# Function to backup database
backup_database() {
    print_status "Starting database backup..."
    
    local backup_file="$1/database_$TIMESTAMP.sql"
    
    # Create database backup
    if [ -n "$DB_PASSWORD" ]; then
        mysqldump -u "$DB_USER" -p"$DB_PASSWORD" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  --complete-insert \
                  --extended-insert \
                  --set-gtid-purged=OFF \
                  "$DB_NAME" > "$backup_file"
    else
        # Use .my.cnf or MySQL config file
        mysqldump -u "$DB_USER" \
                  --single-transaction \
                  --routines \
                  --triggers \
                  --events \
                  --complete-insert \
                  --extended-insert \
                  --set-gtid-purged=OFF \
                  "$DB_NAME" > "$backup_file"
    fi
    
    if [ $? -eq 0 ]; then
        # Compress the SQL dump
        gzip "$backup_file"
        print_success "Database backup completed: ${backup_file}.gz"
        return 0
    else
        print_error "Database backup failed"
        return 1
    fi
}

# Function to backup application files
backup_files() {
    print_status "Starting application files backup..."
    
    local backup_file="$1/files_$TIMESTAMP.tar.gz"
    
    # Change to app directory
    cd "$APP_DIR"
    
    # Create tar archive excluding unnecessary files
    tar -czf "$backup_file" \
        --exclude='node_modules' \
        --exclude='storage/logs/*.log' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='.git' \
        --exclude='vendor' \
        .
    
    if [ $? -eq 0 ]; then
        print_success "Application files backup completed: $backup_file"
        return 0
    else
        print_error "Application files backup failed"
        return 1
    fi
}

# Function to backup uploaded files
backup_uploads() {
    print_status "Starting uploads backup..."
    
    local backup_file="$1/uploads_$TIMESTAMP.tar.gz"
    local uploads_dir="$APP_DIR/storage/app/public"
    
    if [ -d "$uploads_dir" ] && [ "$(ls -A "$uploads_dir")" ]; then
        tar -czf "$backup_file" -C "$APP_DIR/storage/app" public
        
        if [ $? -eq 0 ]; then
            print_success "Uploads backup completed: $backup_file"
            return 0
        else
            print_error "Uploads backup failed"
            return 1
        fi
    else
        print_status "No uploads to backup"
        return 0
    fi
}

# Function to backup configuration files
backup_config() {
    print_status "Starting configuration backup..."
    
    local backup_file="$1/config_$TIMESTAMP.tar.gz"
    
    # Backup important config files (excluding sensitive data)
    tar -czf "$backup_file" \
        -C "$APP_DIR" \
        composer.json \
        composer.lock \
        package.json \
        package-lock.json \
        artisan \
        config/ \
        routes/ \
        database/migrations/ \
        database/seeders/ \
        2>/dev/null || true
    
    # Backup web server configuration
    if [ -f "/etc/nginx/sites-available/finder" ]; then
        tar -rf "${backup_file%.gz}" -C / etc/nginx/sites-available/finder
        gzip -f "${backup_file%.gz}"
    fi
    
    print_success "Configuration backup completed: $backup_file"
}

# Function to encrypt backup
encrypt_backup() {
    local file="$1"
    
    if [ -n "$BACKUP_ENCRYPTION_KEY" ] && command -v openssl >/dev/null 2>&1; then
        print_status "Encrypting backup: $(basename "$file")"
        
        openssl enc -aes-256-cbc -salt -in "$file" -out "${file}.enc" -k "$BACKUP_ENCRYPTION_KEY"
        
        if [ $? -eq 0 ]; then
            rm "$file"  # Remove unencrypted version
            print_success "Backup encrypted: ${file}.enc"
        else
            print_error "Backup encryption failed"
        fi
    fi
}

# Function to upload to S3
upload_to_s3() {
    local backup_dir="$1"
    
    if [ -n "$S3_BUCKET" ] && command -v aws >/dev/null 2>&1; then
        print_status "Uploading backups to S3..."
        
        aws s3 sync "$backup_dir" "s3://$S3_BUCKET/$(basename "$backup_dir")/" \
            --region "$S3_REGION" \
            --profile "$AWS_PROFILE" \
            --storage-class STANDARD_IA
        
        if [ $? -eq 0 ]; then
            print_success "Backups uploaded to S3"
        else
            print_error "S3 upload failed"
        fi
    fi
}

# Function to cleanup old backups
cleanup_old_backups() {
    print_status "Cleaning up old backups..."
    
    # Clean local backups
    find "$BACKUP_BASE_DIR" -name "*.tar.gz" -o -name "*.sql.gz" -o -name "*.enc" | \
        while read -r file; do
            if [ $(stat -c %Y "$file") -lt $(date -d "$RETENTION_DAYS days ago" +%s) ]; then
                rm "$file"
                log "Removed old backup: $file"
            fi
        done
    
    # Clean S3 backups (if configured)
    if [ -n "$S3_BUCKET" ] && command -v aws >/dev/null 2>&1; then
        aws s3 ls "s3://$S3_BUCKET/" --recursive | \
            awk '{print $1, $2, $4}' | \
            while read -r date time file; do
                file_date=$(date -d "$date $time" +%s)
                cutoff_date=$(date -d "$RETENTION_DAYS days ago" +%s)
                
                if [ "$file_date" -lt "$cutoff_date" ]; then
                    aws s3 rm "s3://$S3_BUCKET/$file"
                    log "Removed old S3 backup: $file"
                fi
            done
    fi
    
    print_success "Old backups cleaned up"
}

# Function to verify backup integrity
verify_backup() {
    local backup_file="$1"
    
    print_status "Verifying backup integrity: $(basename "$backup_file")"
    
    case "$backup_file" in
        *.tar.gz)
            if tar -tzf "$backup_file" >/dev/null 2>&1; then
                print_success "Backup integrity verified: $(basename "$backup_file")"
                return 0
            else
                print_error "Backup integrity check failed: $(basename "$backup_file")"
                return 1
            fi
            ;;
        *.sql.gz)
            if gzip -t "$backup_file" 2>/dev/null; then
                print_success "Backup integrity verified: $(basename "$backup_file")"
                return 0
            else
                print_error "Backup integrity check failed: $(basename "$backup_file")"
                return 1
            fi
            ;;
        *)
            print_status "Unknown file type, skipping integrity check"
            return 0
            ;;
    esac
}

# Main backup function
main() {
    local backup_type="$1"
    local backup_dir=""
    
    case "$backup_type" in
        "daily")
            backup_dir="$BACKUP_BASE_DIR/daily"
            ;;
        "weekly")
            backup_dir="$BACKUP_BASE_DIR/weekly"
            ;;
        "monthly")
            backup_dir="$BACKUP_BASE_DIR/monthly"
            ;;
        *)
            backup_type="daily"
            backup_dir="$BACKUP_BASE_DIR/daily"
            ;;
    esac
    
    print_status "Starting $backup_type backup of Finder Laravel application"
    
    # Check prerequisites
    if ! check_disk_space 5; then
        send_notification "Backup failed: Insufficient disk space" "ERROR"
        exit 1
    fi
    
    # Create backup directory for this run
    local current_backup_dir="$backup_dir/backup_$TIMESTAMP"
    mkdir -p "$current_backup_dir"
    
    local failed_backups=0
    
    # Perform backups
    backup_database "$current_backup_dir" || ((failed_backups++))
    backup_files "$current_backup_dir" || ((failed_backups++))
    backup_uploads "$current_backup_dir" || ((failed_backups++))
    backup_config "$current_backup_dir" || ((failed_backups++))
    
    # Verify backup integrity
    for file in "$current_backup_dir"/*; do
        [ -f "$file" ] && verify_backup "$file"
    done
    
    # Encrypt backups if key is provided
    if [ -n "$BACKUP_ENCRYPTION_KEY" ]; then
        for file in "$current_backup_dir"/*.{tar.gz,sql.gz} 2>/dev/null; do
            [ -f "$file" ] && encrypt_backup "$file"
        done
    fi
    
    # Upload to S3
    upload_to_s3 "$current_backup_dir"
    
    # Create backup summary
    local summary_file="$current_backup_dir/backup_summary.txt"
    {
        echo "Finder Laravel Backup Summary"
        echo "============================="
        echo "Backup Type: $backup_type"
        echo "Timestamp: $TIMESTAMP"
        echo "Date: $(date)"
        echo "Failed Backups: $failed_backups"
        echo ""
        echo "Files:"
        ls -lh "$current_backup_dir"
    } > "$summary_file"
    
    # Clean up old backups
    cleanup_old_backups
    
    # Send notification
    if [ "$failed_backups" -eq 0 ]; then
        local message="$backup_type backup completed successfully for Finder Laravel application"
        print_success "$message"
        send_notification "$message" "SUCCESS"
        exit 0
    else
        local message="$backup_type backup completed with $failed_backups failures"
        print_error "$message"
        send_notification "$message" "ERROR"
        exit 1
    fi
}

# Script usage
if [ $# -eq 0 ]; then
    echo "Usage: $0 [daily|weekly|monthly]"
    echo "  daily   - Create daily backup"
    echo "  weekly  - Create weekly backup"
    echo "  monthly - Create monthly backup"
    exit 1
fi

# Run the backup
main "$1"
