# BGAofis Law Office Automation - Backend Deployment Checklist

## Pre-Deployment Checklist

### 1. Environment Preparation
- [ ] Ensure PHP 8.2+ is installed on the server
- [ ] Verify Composer is available on the server
- [ ] Confirm MySQL/MariaDB database is available
- [ ] Check that required PHP extensions are installed:
  - [ ] php-mysql
  - [ ] php-json
  - [ ] php-mbstring
  - [ ] php-openssl
  - [ ] php-curl
  - [ ] php-xml
  - [ ] php-zip

### 2. Files to Upload
Upload the following files and directories to your hosting:

#### Required Files:
- [ ] `composer.json` - Dependency management
- [ ] `composer.lock` - Lock file for exact versions (if exists)
- [ ] `.env` - Environment configuration (create from .env.example)
- [ ] `public/.htaccess` - Apache configuration
- [ ] `public/index.php` - Application entry point

#### Application Directories:
- [ ] `app/` - Application source code
- [ ] `bootstrap/` - Application bootstrap
- [ ] `config/` - Configuration files
- [ ] `database/` - Database migrations and seeders
- [ ] `routes/` - API routes
- [ ] `vendor/` - Composer dependencies (install on server)

#### Create These Directories on Server:
- [ ] `logs/` - Application logs
- [ ] `uploads/` - File uploads
- [ ] `backups/` - Database backups
- [ ] `temp/` - Temporary files

### 3. File Permissions
Set the following permissions after upload:

```bash
# Directories
chmod 755 app/
chmod 755 bootstrap/
chmod 755 config/
chmod 755 database/
chmod 755 routes/
chmod 755 public/
chmod 755 logs/
chmod 755 uploads/
chmod 755 backups/
chmod 755 temp/

# Files
chmod 644 public/index.php
chmod 644 public/.htaccess
chmod 644 composer.json
chmod 600 .env  # Important: Keep this secure
```

### 4. Environment Configuration
- [ ] Copy `.env.example` to `.env`
- [ ] Update `.env` with production values:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL` - Set to your actual domain
  - [ ] Database credentials
  - [ ] JWT secret (generate a new one)
  - [ ] Mail configuration
  - [ ] File paths (update to server paths)

### 5. Installation Steps
Execute these commands in the backend directory:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Run database migrations
php database/migrate.php

# (Optional) Run seeders
php database/seed.php

# Test deployment
php deploy.php
```

### 6. Web Server Configuration

#### Apache Configuration:
- [ ] Ensure `mod_rewrite` is enabled
- [ ] Verify `AllowOverride All` is set for the public directory
- [ ] Document root should point to `public/` directory
- [ ] PHP version should be 8.2+

#### Nginx Configuration (if applicable):
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Security Considerations
- [ ] Verify `.env` file is not web-accessible
- [ ] Check that sensitive files are blocked by `.htaccess`
- [ ] Implement SSL/TLS certificate
- [ ] Set up firewall rules
- [ ] Configure regular backups
- [ ] Monitor error logs

### 8. Testing
- [ ] Test API endpoints with a tool like Postman
- [ ] Verify database connectivity
- [ ] Check file upload functionality
- [ ] Test authentication system
- [ ] Verify CORS headers are working

### 9. Post-Deployment
- [ ] Set up monitoring for application errors
- [ ] Configure log rotation
- [ ] Set up automated backups
- [ ] Document the deployment process
- [ ] Schedule regular security updates

## Troubleshooting Common Issues

### 500 Internal Server Error
1. Check PHP error logs
2. Verify file permissions
3. Ensure `.htaccess` is properly configured
4. Check if all dependencies are installed

### Database Connection Issues
1. Verify database credentials in `.env`
2. Check if database server is running
3. Ensure database user has proper permissions
4. Test with a simple connection script

### File Upload Issues
1. Check `uploads/` directory permissions
2. Verify PHP upload limits
3. Ensure disk space is available
4. Check file size restrictions in `.htaccess`

### CORS Issues
1. Verify CORS headers in `.htaccess`
2. Check if your frontend domain is allowed
3. Test with different browsers

## Quick Deployment Commands

```bash
# Clone or upload files
cd /path/to/backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set up environment
cp .env.example .env
# Edit .env with your values

# Create directories
mkdir -p logs uploads backups temp

# Set permissions
chmod 755 logs uploads backups temp
chmod 600 .env

# Run migrations
php database/migrate.php

# Test deployment
php deploy.php
```

## Support Contacts

- For hosting issues: Contact name.com support
- For application issues: Check application logs
- For database issues: Contact database administrator