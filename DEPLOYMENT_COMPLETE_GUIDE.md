# BGAofis Law Office Automation - Complete Deployment Guide

## Overview

This comprehensive guide provides step-by-step instructions for deploying the complete BGAofis Law Office Automation system (both frontend and backend) to name.com hosting. The system consists of:

- **Backend**: PHP 8.2+ application using Slim framework with MySQL database
- **Frontend**: React/TypeScript SPA built with Vite

## Prerequisites

Before starting, ensure you have:
- Active name.com hosting account with cPanel access
- PHP 8.2+ support (check in cPanel > Select PHP Version)
- MySQL database access
- Node.js (v18 or higher) installed locally
- npm or yarn package manager
- File transfer method (FTP, SFTP, or cPanel File Manager)

---

## Part 1: Database Setup on name.com

### Step 1: Create Database

1. Log in to your name.com cPanel
2. Navigate to **MySQL Databases** or **MariaDB**
3. Create a new database:
   - Database name: `bgaofis_db` (or your preferred name)
   - Note the full database name (includes cPanel username prefix)

### Step 2: Create Database User

1. In the same MySQL Databases section:
   - Username: `bgaofis_user` (or your preferred name)
   - Generate a strong password
2. Add the user to the database with all privileges
3. Note down:
   - Database name (e.g., `username_bgaofis_db`)
   - Database username (e.g., `username_bgaofis_user`)
   - Database password
   - Database host (usually `localhost`)

---

## Part 2: Backend Deployment

### Step 3: Configure Backend Environment

1. Open the local backend `.env.example` file as a template
2. Create a new `.env` file with production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/backend

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourusername_bgaofis_db
DB_USERNAME=yourusername_bgaofis_user
DB_PASSWORD=your_strong_password

# Generate a new JWT secret
JWT_SECRET=generate_a_new_long_random_string_here

# Update file paths for name.com hosting
FILES_UPLOAD_PATH=/home/yourusername/public_html/backend/uploads
BACKUP_PATH=/home/yourusername/public_html/backend/backups
```

### Step 4: Upload Backend Files

1. In cPanel, open **File Manager**
2. Navigate to `public_html/`
3. Create a new folder named `backend`
4. Upload the following files and folders to `public_html/backend/`:

#### Required Files:
- `composer.json`
- `composer.lock` (if exists)
- `.env` (your configured environment file)
- `public/.htaccess`
- `public/index.php`
- `deploy.php`

#### Application Folders:
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `routes/`

### Step 5: Install Backend Dependencies

1. In cPanel, look for **Terminal** or **SSH Access**
2. If Terminal is available:
   ```bash
   cd public_html/backend
   composer install --no-dev --optimize-autoloader
   ```
3. If Terminal is not available:
   - Use cPanel's **Composer** tool if available
   - Or install Composer locally and upload the `vendor/` folder

### Step 6: Set Backend File Permissions

1. Right-click on folders and select **Change Permissions**
2. Set the following permissions:

#### Directories (755):
- `app/` - 755
- `bootstrap/` - 755
- `config/` - 755
- `database/` - 755
- `routes/` - 755
- `public/` - 755
- `vendor/` - 755

#### Files (644):
- `public/index.php` - 644
- `public/.htaccess` - 644
- `composer.json` - 644

#### Sensitive File (600):
- `.env` - 600 (very important for security)

### Step 7: Create Additional Directories

1. Create these directories inside `backend/`:
   - `logs/`
   - `uploads/`
   - `backups/`
   - `temp/`
2. Set their permissions to 755

---

## Part 3: Frontend Deployment

### Step 8: Prepare Frontend Build

1. On your local machine, navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Configure environment variables for production:
   Create/update `.env` file with:
   ```env
   # API Configuration
   VITE_API_URL=https://yourdomain.com/backend/api

   # Application Configuration
   VITE_APP_NAME="BGAofis Hukuk Otomasyon"
   VITE_APP_VERSION="1.0.0"

   # Feature Flags
   VITE_ENABLE_ANALYTICS=false
   VITE_ENABLE_DEBUG=false

   # Document Upload Configuration
   VITE_MAX_FILE_SIZE_MB=10
   VITE_ALLOWED_FILE_TYPES=.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png
   ```

4. Build for production:
   ```bash
   npm run build
   ```

### Step 9: Upload Frontend Files

1. After building, upload the entire contents of the `dist/` directory to your web root (`public_html/`)
2. The directory structure should be:
   ```
   public_html/
   ├── index.html
   ├── assets/
   │   ├── index-[hash].css
   │   ├── index-[hash].js
   │   └── [other asset files]
   └── .htaccess (create this file - see next step)
   ```

### Step 10: Create Frontend .htaccess

Create a `.htaccess` file in `public_html/` with the following content:

```apache
# Enable rewrite engine
RewriteEngine On

# Handle React Router - redirect all requests to index.html
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.html [L]

# Set proper MIME types
<IfModule mod_mime.c>
  AddType text/javascript .js
  AddType text/css .css
  AddType image/svg+xml .svg
  AddType image/x-icon .ico
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/plain
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE text/xml
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE application/xml
  AddOutputFilterByType DEFLATE application/xhtml+xml
  AddOutputFilterByType DEFLATE application/rss+xml
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/ico "access plus 1 year"
  ExpiresByType image/icon "access plus 1 year"
  ExpiresByType text/html "access plus 600 seconds"
</IfModule>

# Security headers
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options nosniff
  Header always set X-Frame-Options DENY
  Header always set X-XSS-Protection "1; mode=block"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

---

## Part 4: Database Migration and Testing

### Step 11: Run Database Migrations

1. In cPanel Terminal or SSH:
   ```bash
   cd public_html/backend
   php database/migrate.php
   ```

2. If Terminal is not available:
   - Access `https://yourdomain.com/backend/public/migrate.php` in your browser
   - Or create a temporary PHP script to run migrations

### Step 12: Test Backend API

1. Open your browser and navigate to:
   `https://yourdomain.com/backend/`

2. You should see a JSON response or an error (check logs)

3. Test API endpoints:
   - `GET https://yourdomain.com/backend/api/auth/login`
   - Check for proper JSON responses

4. Run the deployment check script:
   ```bash
   php deploy.php
   ```

### Step 13: Test Frontend Application

1. Navigate to your domain: `https://yourdomain.com`
2. Verify the application loads correctly
3. Test authentication flow
4. Test different routes and features
5. Check browser console for any errors

---

## Part 5: Final Configuration and Security

### Step 14: Configure CORS

Ensure your backend allows requests from your frontend domain. Update the backend `.htaccess` or add to your PHP code:

```apache
# Add to backend/public/.htaccess
<IfModule mod_headers.c>
  Header always set Access-Control-Allow-Origin "https://yourdomain.com"
  Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
  Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>
```

### Step 15: SSL Certificate

1. Ensure SSL is enabled for your domain
2. In cPanel, go to **Let's Encrypt** or **SSL/TLS Status**
3. Install or renew SSL certificate if needed

### Step 16: Final Security Checks

1. Verify `.env` is not accessible via browser:
   - Try accessing `https://yourdomain.com/backend/.env`
   - Should show 403 Forbidden or 404 Not Found

2. Check error logs for any issues:
   - In cPanel, go to **Metrics** > **Errors**

3. Verify file permissions are correct

---

## Deployment Verification Checklist

### Backend Verification
- [ ] Database created and user configured with proper privileges
- [ ] Backend files uploaded to `public_html/backend/`
- [ ] Composer dependencies installed successfully
- [ ] File permissions set correctly (755 for directories, 644 for files, 600 for .env)
- [ ] Required directories created (logs, uploads, backups, temp)
- [ ] Database migrations executed successfully
- [ ] API endpoints responding correctly
- [ ] Deployment check script passes

### Frontend Verification
- [ ] Production build created successfully
- [ ] All build files uploaded to `public_html/`
- [ ] `.htaccess` file created and configured for SPA routing
- [ ] Application loads correctly in browser
- [ ] All routes are accessible (test direct URL navigation)
- [ ] API calls are working correctly
- [ ] Authentication flow is working
- [ ] File uploads are working (if applicable)

### Integration Verification
- [ ] Frontend can successfully communicate with backend API
- [ ] CORS headers configured correctly
- [ ] HTTPS/SSL is working properly
- [ ] Error pages are handled gracefully
- [ ] Security headers are in place

---

## Troubleshooting Common Issues

### Backend Issues

#### 1. PHP Version Too Old
- **Symptoms**: 500 errors, PHP syntax errors
- **Solution**: In cPanel, go to **Select PHP Version** and choose PHP 8.2+

#### 2. Composer Not Available
- **Symptoms**: Vendor directory missing, class not found errors
- **Solution**: Install Composer locally and upload the `vendor/` folder

#### 3. Database Connection Failed
- **Symptoms**: Database connection errors
- **Solution**: 
  - Verify database credentials include cPanel username prefix
  - Check that database user has all privileges on the database
  - Ensure database name is correct

#### 4. 500 Internal Server Error
- **Symptoms**: Generic server error
- **Solution**:
  - Check cPanel error logs
  - Verify file permissions
  - Ensure `.htaccess` is properly configured

### Frontend Issues

#### 1. 404 Errors on Page Refresh
- **Symptoms**: Direct navigation to routes returns 404 errors
- **Solution**: Ensure `.htaccess` file is properly configured and uploaded

#### 2. API Connection Errors
- **Symptoms**: Frontend cannot connect to backend API
- **Solution**: 
  - Verify API URL in environment variables
  - Check CORS configuration on backend
  - Ensure HTTPS is properly configured

#### 3. White Screen/Blank Page
- **Symptoms**: Application loads but shows blank screen
- **Solution**:
  - Check browser console for JavaScript errors
  - Verify all asset files are uploaded
  - Check MIME types in `.htaccess`

#### 4. Styles Not Loading
- **Symptoms**: Application loads but without styling
- **Solution**:
  - Verify CSS files are uploaded
  - Check paths in `index.html`
  - Clear browser cache

### Integration Issues

#### 1. CORS Errors
- **Symptoms**: Cross-origin request blocked
- **Solution**: Configure CORS headers in backend `.htaccess`

#### 2. Authentication Issues
- **Symptoms**: Login fails, tokens not working
- **Solution**:
  - Verify JWT secret is set correctly
  - Check API endpoints are accessible
  - Ensure cookies/sessions are configured properly

---

## Maintenance and Monitoring

### Regular Tasks

1. **Backup Database**:
   - Use cPanel > **Backup** > **Download a MySQL Database Backup**
   - Schedule regular automated backups

2. **Update Dependencies**:
   ```bash
   # Backend
   composer update --no-dev
   
   # Frontend
   npm update
   ```

3. **Monitor Logs**:
   - Check cPanel > **Metrics** > **Errors** regularly
   - Monitor application logs

4. **Security Updates**:
   - Keep PHP version updated
   - Update dependencies regularly
   - Run security audits

### Optional Enhancements

1. **Set up Cron Jobs**:
   - For regular database backups
   - For clearing temporary files

2. **Enable Caching**:
   - Configure OPcache in cPanel PHP settings
   - Consider browser caching optimization

3. **Monitoring**:
   - Set up uptime monitoring
   - Configure email alerts for errors

---

## Emergency Rollback Procedures

If something goes wrong after deployment:

1. **Restore Database**:
   - Use cPanel > **Backup Wizard** to restore database
   - Ensure you have recent backups

2. **Revert Code Changes**:
   - Restore previous version from backup
   - Revert `.env` to previous version if needed

3. **Check Logs**:
   - Review error logs for specific issues
   - Identify the root cause

4. **Contact Support**:
   - Contact name.com support if needed
   - Document the issue for future reference

---

## Support Resources

- **name.com Support**: https://www.name.com/support
- **cPanel Documentation**: https://docs.cpanel.net/
- **PHP Documentation**: https://www.php.net/docs.php
- **Slim Framework**: https://www.slimframework.com/
- **React Documentation**: https://react.dev/
- **Vite Documentation**: https://vitejs.dev/

---

## Contact Information

Keep these handy:
- name.com support contact
- Database credentials (stored securely)
- Application credentials
- Backup locations
- Development team contacts

---

## Deployment Quick Reference

### Commands Summary

```bash
# Backend
cd backend
composer install --no-dev --optimize-autoloader
php database/migrate.php
php deploy.php

# Frontend (local)
cd frontend
npm install
npm run build
```

### Key File Locations

- Backend: `public_html/backend/`
- Frontend: `public_html/`
- Database: MySQL via cPanel
- Logs: `public_html/backend/logs/`
- Uploads: `public_html/backend/uploads/`

### Important URLs

- Frontend Application: `https://yourdomain.com`
- Backend API: `https://yourdomain.com/backend/api`
- Database Migration: `https://yourdomain.com/backend/public/migrate.php`