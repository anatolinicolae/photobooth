# Laravel Deployment Guide

This guide covers deploying the Mollieween Scary Shots Laravel application to a production server.

## Prerequisites

- Server with PHP 8.3+, Nginx/Apache, and SQLite or MySQL
- SFTP access credentials
- SSH access for running commands

## GitHub Secrets Configuration

Add these secrets to your GitHub repository (Settings → Secrets and variables → Actions):

- `SFTP_USER` - Your SFTP username
- `SFTP_HOST` - Your server hostname or IP
- `SFTP_PASS` - Your SFTP password
- `REMOTE_PATH` - Remote path where Laravel app will be deployed (e.g., `/var/www/photobooth`)

## Automated Deployment

The GitHub Actions workflow automatically deploys on push to `main` or `anatoli/laravel-backend` branches.

### What Gets Deployed

✅ **Included in deployment:**
- Application code (app/, routes/, config/, etc.)
- Vendor dependencies (optimized, no dev packages)
- Public assets
- Empty storage directory structure

❌ **Excluded from deployment (preserved on server):**
- `storage/app/public/images/` - Uploaded images
- `storage/logs/` - Application logs
- `storage/framework/` - Cache, sessions, views
- `database/database.sqlite` - SQLite database
- `.env` - Environment configuration

## Server Setup (First Time Only)

### 1. Create Directory Structure

```bash
ssh user@yourserver.com

# Create application directory
sudo mkdir -p /var/www/photobooth
sudo chown $USER:www-data /var/www/photobooth

# Create storage directories
cd /var/www/photobooth
mkdir -p storage/app/public/images
mkdir -p storage/logs
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
mkdir -p database

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 2. Create Environment File

```bash
cd /var/www/photobooth
nano .env
```

Add your production configuration:

```env
APP_NAME="Mollieween Scary Shots"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yoursite.com

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/photobooth/database/database.sqlite

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=daily
LOG_LEVEL=warning
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Create Database

```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite
```

### 5. Run Migrations

```bash
php artisan migrate --force
```

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Configure Web Server

**Nginx Example (`/etc/nginx/sites-available/photobooth`):**

```nginx
server {
    listen 80;
    server_name yoursite.com;
    root /var/www/photobooth/public;

    index index.php index.html;

    charset utf-8;

    # Increase client body size for image uploads (100MB)
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP handling - with SSE optimizations
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_index index.php;
        
        # SSE-specific settings
        fastcgi_buffering off;
        fastcgi_read_timeout 3600s;
        fastcgi_send_timeout 3600s;
        
        # Add headers for SSE
        add_header Cache-Control 'no-cache';
        add_header X-Accel-Buffering 'no';
        
        # Standard settings
        fastcgi_buffer_size 32k;
        fastcgi_buffers 8 16k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/photobooth /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 8. Configure PHP

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 256M
max_execution_time = 300
output_buffering = Off
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

## Post-Deployment Steps (After Each Deploy)

After each automated deployment, SSH into your server and run:

```bash
cd /var/www/photobooth

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if needed)
php artisan migrate --force

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

## Manual Deployment (Alternative)

If you need to deploy manually:

```bash
# On your local machine
cd laravel
composer install --no-dev --optimize-autoloader
tar -czf deploy.tar.gz \
  --exclude='storage/app/public/images' \
  --exclude='storage/logs/*' \
  --exclude='database/database.sqlite' \
  --exclude='.env' \
  --exclude='node_modules' \
  --exclude='.git' \
  .

# Upload to server
scp deploy.tar.gz user@server:/var/www/photobooth/

# On server
cd /var/www/photobooth
tar -xzf deploy.tar.gz
rm deploy.tar.gz

# Run post-deployment steps
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
```

## Troubleshooting

### Permission Issues

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Locked

```bash
# Make sure SQLite file is writable
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite
```

### Images Not Displaying

```bash
# Recreate storage link
php artisan storage:link
chmod -R 775 storage/app/public
```

### SSE Not Working

- Check Nginx configuration has `fastcgi_buffering off`
- Ensure PHP output_buffering is Off
- Verify timeouts are set properly

## Monitoring

### Check Logs

```bash
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

### Check PHP-FPM Status

```bash
sudo systemctl status php8.3-fpm
```

### Check Disk Space

```bash
df -h
du -sh storage/app/public/images/*
```

## Backup

### Backup Database

```bash
cp database/database.sqlite database/database.sqlite.backup
```

### Backup Images

```bash
tar -czf images-backup-$(date +%Y%m%d).tar.gz storage/app/public/images/
```

## Restore

### Restore Database

```bash
cp database/database.sqlite.backup database/database.sqlite
```

### Restore Images

```bash
tar -xzf images-backup-YYYYMMDD.tar.gz -C /
```
