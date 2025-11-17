# BGAofis Authentication Deployment Checklist

## Pre-Deployment Checklist
- [ ] PHP 8.0+ installed with required extensions
- [ ] MySQL/MariaDB database created
- [ ] Database user with proper permissions
- [ ] Web server configured

## Server Setup
### PHP Extensions Required
```bash
# Ubuntu/Debian
sudo apt-get install php8.2-mysql php8.2-pdo php8.2-openssl php8.2-mbstring

# CentOS/RHEL
sudo yum install php-mysql php-pdo php-openssl php-mbstring

# Windows (php.ini)
extension=pdo_mysql
extension=openssl
extension=mbstring
```

### Database Setup
```sql
CREATE DATABASE haslim_bgofis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'haslim_bgofis'@'localhost' IDENTIFIED BY 'Fener1907****';
GRANT ALL PRIVILEGES ON haslim_bgofis.* TO 'haslim_bgofis'@'localhost';
FLUSH PRIVILEGES;
```

## File Deployment
1. Upload all backend files to server
2. Set proper permissions:
   ```bash
   chmod 755 -R /path/to/backend
   chmod 777 -R /path/to/backend/storage
   ```
3. Configure web server to point to public/ directory
4. Ensure .env file has correct credentials

## Testing
1. Test database connection:
   ```bash
   cd /path/to/backend
   php validate-env.php
   ```

2. Test authentication:
   ```bash
   php test-auth.php
   ```

3. Test via API:
   ```bash
   curl -X POST https://yourdomain.com/test-auth-endpoint.php \
        -H "Content-Type: application/json" \
        -d '{"test": true}'
   ```

## Common Issues & Solutions

### 401 Unauthorized Errors
- Check JWT_SECRET is consistent across all env files
- Verify token hasn't expired
- Ensure Authorization header format: "Bearer <token>"
- Check server time synchronization

### Database Connection Issues
- Install pdo_mysql extension
- Verify database credentials in .env
- Check database server is running
- Test with mysql command line client

### Permission Issues
- Check file permissions on storage directory
- Verify web server user has read access
- Ensure .env file is readable by web server

## Production Security
- Change default JWT secret to random string
- Use HTTPS for all API calls
- Implement rate limiting
- Regular security updates
- Monitor error logs
