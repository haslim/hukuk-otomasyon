# BGAofis Law Office Automation - name.com Hosting Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the BGAofis Law Office Automation backend to name.com shared hosting. The backend is a PHP 8.2+ application using the Slim framework with MySQL database.

## Prerequisites

- Active name.com hosting account with cPanel access
- PHP 8.2+ support (check in cPanel > Select PHP Version)
- MySQL database access
- File transfer method (FTP, SFTP, or cPanel File Manager)

## Step 1: Prepare Your Local Environment

1. Ensure your backend is working locally
2. Update your `.env` file with production-ready values
3. Run `composer install` to generate `composer.lock` if not present

## Step 2: Set Up Database on name.com

1. Log in to your name.com cPanel
2. Navigate to **MySQL Databases** or **MariaDB**
3. Create a new database:
   - Database name: `bgaofis_db` (or your preferred name)
   - Note the full database name (includes cPanel username prefix)
4. Create a database user:
   - Username: `bgaofis_user` (or your preferred name)
   - Generate a strong password
5. Add the user to the database with all privileges
6. Note down:
   - Database name (e.g., `username_bgaofis_db`)
   - Database username (e.g., `username_bgaofis_user`)
   - Database password
   - Database host (usually `localhost`)

## Step 3: Configure Environment

1. Open your local `.env` file
2. Update the following values with your name.com database details:

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

3. Save the updated `.env` file

## Step 4: Upload Files to name.com

### Option A: Using cPanel File Manager

1. Log in to cPanel
2. Open **File Manager**
3. Navigate to `public_html/`
4. Create a new folder named `backend`
5. Upload the following files and folders to `public_html/backend/`:

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

### Option B: Using FTP/SFTP

1. Connect to your hosting account
2. Navigate to `public_html/`
3. Create a `backend` directory
4. Upload all the files and folders listed above

## Step 5: Install Dependencies

1. In cPanel, look for **Terminal** or **SSH Access**
2. If Terminal is available:
   ```bash
   cd public_html/backend
   composer install --no-dev --optimize-autoloader
   ```
3. If Terminal is not available:
   - Use cPanel's **Composer** tool if available
   - Or install Composer locally and upload the `vendor/` folder

## Step 6: Set File Permissions

### Using cPanel File Manager:

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

### Create Additional Directories:

1. Create these directories inside `backend/`:
   - `logs/`
   - `uploads/`
   - `backups/`
   - `temp/`

2. Set their permissions to 755

## Step 7: Run Database Migrations

1. In cPanel Terminal or SSH:
   ```bash
   cd public_html/backend
   php database/migrate.php
   ```

2. If Terminal is not available, you may need to:
   - Create a temporary PHP script to run migrations
   - Or contact name.com support for assistance

## Step 8: Configure Web Server

### For Apache (most common on name.com):

1. The `.htaccess` file in `public/` should handle URL rewriting
2. Verify that `mod_rewrite` is enabled (usually is on name.com)
3. Ensure your domain points to the `public_html/backend/public/` directory

### Subdomain Setup (Optional):

If you want to use a subdomain like `api.yourdomain.com`:

1. In cPanel, go to **Domains** > **Subdomains**
2. Create a subdomain pointing to `public_html/backend/public/`
3. Update `APP_URL` in `.env` to `https://api.yourdomain.com`

## Step 9: Test the Deployment

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

## Step 10: Final Security Checks

1. Verify `.env` is not accessible via browser:
   - Try accessing `https://yourdomain.com/backend/.env`
   - Should show 403 Forbidden or 404 Not Found

2. Check error logs for any issues:
   - In cPanel, go to **Metrics** > **Errors**

3. Set up SSL certificate if not already:
   - Use cPanel's **Let's Encrypt** or **SSL/TLS Status**

## Common name.com Issues and Solutions

### Issue 1: PHP Version Too Old
- Solution: In cPanel, go to **Select PHP Version** and choose PHP 8.2+

### Issue 2: Composer Not Available
- Solution: Install Composer locally and upload the `vendor/` folder
- Alternative: Use cPanel's **Setup PHP App** tool

### Issue 3: Database Connection Failed
- Solution: Verify database credentials include cPanel username prefix
- Check that database user has all privileges on the database

### Issue 4: 500 Internal Server Error
- Check cPanel error logs
- Verify file permissions
- Ensure `.htaccess` is properly configured

### Issue 5: CORS Errors
- Verify CORS headers in `.htaccess`
- Update `APP_URL` in `.env` to match your frontend domain

## Maintenance and Monitoring

### Regular Tasks:

1. **Backup Database**:
   - Use cPanel > **Backup** > **Download a MySQL Database Backup**

2. **Update Dependencies**:
   ```bash
   composer update --no-dev
   ```

3. **Monitor Logs**:
   - Check cPanel > **Metrics** > **Errors** regularly

4. **Security Updates**:
   - Keep PHP version updated
   - Update dependencies regularly

### Optional Enhancements:

1. **Set up Cron Jobs**:
   - For regular database backups
   - For clearing temporary files

2. **Enable Caching**:
   - Configure OPcache in cPanel PHP settings
   - Consider Redis if available

3. **Monitoring**:
   - Set up uptime monitoring
   - Configure email alerts for errors

## Support Resources

- **name.com Support**: https://www.name.com/support
- **cPanel Documentation**: https://docs.cpanel.net/
- **PHP Documentation**: https://www.php.net/docs.php
- **Slim Framework**: https://www.slimframework.com/

## Emergency Rollback

If something goes wrong:

1. Restore from backup (cPanel > **Backup Wizard**)
2. Revert `.env` to previous version
3. Check error logs for specific issues
4. Contact name.com support if needed

## Contact Information

Keep these handy:
- name.com support contact
- Database credentials (stored securely)
- Application credentials
- Backup locations