#!/bin/bash
# Finder Laravel Application - Health Check & Monitoring Script
#
# This script monitors the health of the Finder Laravel application
# and can be run via cron job for continuous monitoring.

set -e

# Configuration
APP_DIR="/var/www/html/finder"
LOG_FILE="/var/log/finder-health.log"
ALERT_EMAIL="admin@your-domain.com"
SLACK_WEBHOOK="https://hooks.slack.com/your-webhook-url"
PHP_BINARY="/usr/bin/php8.2"

# Health check endpoints
APP_URL="https://your-domain.com"
HEALTH_ENDPOINT="$APP_URL/health"
API_ENDPOINT="$APP_URL/api/health"

# Thresholds
MAX_RESPONSE_TIME=5000  # milliseconds
MAX_CPU_USAGE=80       # percentage
MAX_MEMORY_USAGE=80    # percentage
MAX_DISK_USAGE=85      # percentage
MIN_FREE_SPACE=1048576 # 1GB in KB

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a $LOG_FILE
}

# Function to send alerts
send_alert() {
    local message="$1"
    local level="$2"
    
    log "ALERT [$level]: $message"
    
    # Send email alert
    if command -v mail >/dev/null 2>&1; then
        echo "$message" | mail -s "Finder Laravel Alert [$level]" "$ALERT_EMAIL"
    fi
    
    # Send Slack notification
    if [ -n "$SLACK_WEBHOOK" ] && command -v curl >/dev/null 2>&1; then
        curl -X POST "$SLACK_WEBHOOK" \
             -H 'Content-type: application/json' \
             --data "{\"text\":\"🚨 Finder Laravel Alert [$level]: $message\"}" \
             --silent --output /dev/null
    fi
}

# Function to check HTTP response
check_http_response() {
    local url="$1"
    local expected_code="$2"
    local timeout="$3"
    
    if command -v curl >/dev/null 2>&1; then
        response=$(curl -s -o /dev/null -w "%{http_code},%{time_total}" --max-time "$timeout" "$url")
        http_code=$(echo "$response" | cut -d',' -f1)
        response_time=$(echo "$response" | cut -d',' -f2)
        response_time_ms=$(echo "$response_time * 1000" | bc)
        
        if [ "$http_code" = "$expected_code" ]; then
            if (( $(echo "$response_time_ms > $MAX_RESPONSE_TIME" | bc -l) )); then
                send_alert "Slow response from $url: ${response_time_ms}ms (threshold: ${MAX_RESPONSE_TIME}ms)" "WARNING"
                return 1
            else
                log "HTTP check passed for $url (${response_time_ms}ms)"
                return 0
            fi
        else
            send_alert "HTTP check failed for $url: Got $http_code, expected $expected_code" "CRITICAL"
            return 1
        fi
    else
        log "curl not available, skipping HTTP checks"
        return 1
    fi
}

# Function to check database connectivity
check_database() {
    cd "$APP_DIR"
    
    if $PHP_BINARY artisan migrate:status >/dev/null 2>&1; then
        log "Database connection check passed"
        return 0
    else
        send_alert "Database connection failed" "CRITICAL"
        return 1
    fi
}

# Function to check queue status
check_queue() {
    cd "$APP_DIR"
    
    # Check if queue workers are running
    if pgrep -f "artisan queue:work" >/dev/null; then
        log "Queue workers are running"
        
        # Check for failed jobs
        failed_jobs=$($PHP_BINARY artisan queue:failed --format=json | jq '. | length' 2>/dev/null || echo "0")
        if [ "$failed_jobs" -gt 0 ]; then
            send_alert "Found $failed_jobs failed queue jobs" "WARNING"
        fi
        
        return 0
    else
        send_alert "No queue workers running" "WARNING"
        return 1
    fi
}

# Function to check Redis connectivity
check_redis() {
    if command -v redis-cli >/dev/null 2>&1; then
        if redis-cli ping >/dev/null 2>&1; then
            log "Redis connection check passed"
            return 0
        else
            send_alert "Redis connection failed" "CRITICAL"
            return 1
        fi
    else
        log "redis-cli not available, skipping Redis check"
        return 1
    fi
}

# Function to check system resources
check_system_resources() {
    # Check CPU usage
    cpu_usage=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
    if (( $(echo "$cpu_usage > $MAX_CPU_USAGE" | bc -l) )); then
        send_alert "High CPU usage: ${cpu_usage}% (threshold: ${MAX_CPU_USAGE}%)" "WARNING"
    else
        log "CPU usage: ${cpu_usage}%"
    fi
    
    # Check memory usage
    memory_info=$(free | grep Mem)
    total_mem=$(echo "$memory_info" | awk '{print $2}')
    used_mem=$(echo "$memory_info" | awk '{print $3}')
    memory_usage=$(echo "scale=2; $used_mem * 100 / $total_mem" | bc)
    
    if (( $(echo "$memory_usage > $MAX_MEMORY_USAGE" | bc -l) )); then
        send_alert "High memory usage: ${memory_usage}% (threshold: ${MAX_MEMORY_USAGE}%)" "WARNING"
    else
        log "Memory usage: ${memory_usage}%"
    fi
    
    # Check disk usage
    disk_usage=$(df "$APP_DIR" | tail -1 | awk '{print $5}' | sed 's/%//')
    if [ "$disk_usage" -gt "$MAX_DISK_USAGE" ]; then
        send_alert "High disk usage: ${disk_usage}% (threshold: ${MAX_DISK_USAGE}%)" "WARNING"
    else
        log "Disk usage: ${disk_usage}%"
    fi
    
    # Check free space
    free_space=$(df "$APP_DIR" | tail -1 | awk '{print $4}')
    if [ "$free_space" -lt "$MIN_FREE_SPACE" ]; then
        send_alert "Low disk space: ${free_space}KB (minimum: ${MIN_FREE_SPACE}KB)" "CRITICAL"
    else
        log "Free disk space: ${free_space}KB"
    fi
}

# Function to check log files for errors
check_logs() {
    local log_dir="$APP_DIR/storage/logs"
    local error_count=0
    
    # Check Laravel logs for errors in the last 10 minutes
    if [ -f "$log_dir/laravel.log" ]; then
        error_count=$(grep -c "ERROR\|CRITICAL\|EMERGENCY" "$log_dir/laravel.log" | tail -10 || echo "0")
        if [ "$error_count" -gt 0 ]; then
            send_alert "Found $error_count recent errors in Laravel logs" "WARNING"
        fi
    fi
    
    # Check web server error logs
    local nginx_error_log="/var/log/nginx/finder_error.log"
    if [ -f "$nginx_error_log" ]; then
        recent_errors=$(grep "$(date -d '10 minutes ago' '+%Y/%m/%d %H:%M')" "$nginx_error_log" | wc -l)
        if [ "$recent_errors" -gt 0 ]; then
            send_alert "Found $recent_errors recent errors in Nginx logs" "WARNING"
        fi
    fi
}

# Function to check SSL certificate expiry
check_ssl_certificate() {
    if command -v openssl >/dev/null 2>&1; then
        cert_info=$(echo | openssl s_client -servername your-domain.com -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)
        
        if [ $? -eq 0 ]; then
            expiry_date=$(echo "$cert_info" | grep "notAfter" | cut -d= -f2)
            expiry_timestamp=$(date -d "$expiry_date" +%s)
            current_timestamp=$(date +%s)
            days_until_expiry=$(( (expiry_timestamp - current_timestamp) / 86400 ))
            
            if [ "$days_until_expiry" -lt 30 ]; then
                send_alert "SSL certificate expires in $days_until_expiry days" "WARNING"
            else
                log "SSL certificate valid for $days_until_expiry days"
            fi
        else
            send_alert "Failed to check SSL certificate" "WARNING"
        fi
    fi
}

# Function to check file permissions
check_permissions() {
    local issues=0
    
    # Check storage directory permissions
    if [ ! -w "$APP_DIR/storage" ]; then
        send_alert "Storage directory is not writable" "CRITICAL"
        ((issues++))
    fi
    
    # Check bootstrap/cache permissions
    if [ ! -w "$APP_DIR/bootstrap/cache" ]; then
        send_alert "Bootstrap cache directory is not writable" "CRITICAL"
        ((issues++))
    fi
    
    if [ "$issues" -eq 0 ]; then
        log "File permissions check passed"
    fi
    
    return $issues
}

# Main health check function
main() {
    log "Starting health check for Finder Laravel application"
    
    local failed_checks=0
    
    # HTTP Response checks
    check_http_response "$APP_URL" "200" "10" || ((failed_checks++))
    
    # Database connectivity
    check_database || ((failed_checks++))
    
    # Redis connectivity
    check_redis || ((failed_checks++))
    
    # Queue status
    check_queue || ((failed_checks++))
    
    # System resources
    check_system_resources
    
    # Log file analysis
    check_logs
    
    # SSL certificate
    check_ssl_certificate
    
    # File permissions
    check_permissions || ((failed_checks++))
    
    # Overall health status
    if [ "$failed_checks" -eq 0 ]; then
        log "All health checks passed ✓"
        exit 0
    else
        log "$failed_checks health check(s) failed"
        exit 1
    fi
}

# Run the health check
main "$@"
