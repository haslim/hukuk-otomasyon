# Complete Production Deployment Guide

## Issues Identified

1. **405 Method Not Allowed errors** - FIXED ✅
   - Root cause: OPTIONS handler registered after routing middleware
   - Solution: Moved OPTIONS handler before routing middleware

2. **401 Unauthorized login errors** - DIAGNOSED ⚠️
   - Root cause: Missing or misconfigured environment variables
   - Solution: Proper .env file setup and database configuration

## Deployment Steps

### Phase 1: Environment Setup

1. **Set up .env file**:
   ```bash
   php setup-production-env.php
   ```

2. **Update production values in .env**:
   - `DB_HOST` - Production database host
   - `DB_DATABASE` - Production database name  
   - `DB_USERNAME` - Production database username
   - `DB_PASSWORD` - Production database password
   - `JWT_SECRET` - Secure JWT secret (auto-generated)
   - `APP_URL` - Production application URL

### Phase 2: Database Setup

1. **Test database connection**:
   ```bash
   php test-auth-database.php
   ```

2. **Run migrations** (if needed):
   ```bash
   php database/migrate.php
   ```

3. **Run seeds** (if no users exist):
   ```bash
   php database/seed.php
   ```

### Phase 3: CORS Fix Deployment

1. **Apply CORS fix**:
   ```bash
   php deploy-cors-fix-production.php
   ```

2. **Verify routes work**:
   ```bash
   php test-api-routes.php
   ```

### Phase 4: Verification

1. **Test authentication endpoints**:
   ```bash
   # Test login
   curl -X POST "https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"email":"your-email@example.com","password":"your-password"}'
   
   # Test API with token
   curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard" \
        -H "Authorization: Bearer YOUR_JWT_TOKEN" \
        -H "Content-Type: application/json"
   ```

2. **Test frontend application**
   - Check for 405 errors (should be resolved)
   - Check for 401 errors (should work with valid credentials)

## Files Created/Modified

### New Files:
- `backend/setup-production-env.php` - Environment setup script
- `backend/test-auth-database.php` - Authentication diagnostic tool
- `backend/deploy-cors-fix-production.php` - Production CORS fix script
- `backend/COMPLETE_PRODUCTION_DEPLOYMENT_GUIDE.md` - This guide

### Modified Files:
- `backend/bootstrap/app.php` - CORS middleware order fixed
- `backend/deploy-cors-fix.php` - Local testing script updated

## Troubleshooting

### If 405 errors persist:
1. Verify CORS fix was applied:
   ```bash
   grep -n "options.*routes" bootstrap/app.php
   ```
2. Check web server configuration
3. Restart web server

### If 401 errors persist:
1. Check environment variables:
   ```bash
   php test-auth-database.php
   ```
2. Verify database has users:
   ```sql
   SELECT COUNT(*) FROM users;
   ```
3. Check JWT secret is configured
4. Test with correct credentials

### If database connection fails:
1. Verify PDO MySQL extension is installed:
   ```bash
   php -m | grep pdo_mysql
   ```
2. Check database credentials in .env
3. Verify database server is accessible
4. Check database permissions

### If no users exist:
1. Run database seeds:
   ```bash
   php database/seed.php
   ```
2. Or create admin user manually:
   ```sql
   INSERT INTO users (email, password, name, email_verified_at, created_at, updated_at) 
   VALUES ('admin@example.com', '$2y$12$...', 'Admin User', NOW(), NOW(), NOW());
   ```

## Security Considerations

1. **JWT Secret**: Use a strong, unique JWT secret
2. **Database Credentials**: Use strong passwords and limited privileges
3. **File Permissions**: Secure .env file (600 permissions)
4. **HTTPS**: Ensure production uses HTTPS
5. **Environment Variables**: Never commit .env to version control

## Expected Results

After deployment:

1. ✅ **OPTIONS requests** return HTTP 200 with CORS headers
2. ✅ **GET requests** work for authenticated users  
3. ✅ **Login works** with valid credentials
4. ✅ **API endpoints** return proper responses
5. ✅ **Frontend loads** without 405/401 errors

## Rollback Plan

If something goes wrong:

1. **Restore .env backup**:
   ```bash
   cp .env.backup.YYYY-MM-DD-HH-MM-SS .env
   ```

2. **Restore bootstrap backup**:
   ```bash
   cp bootstrap/app.php.backup.YYYY-MM-DD-HH-MM-SS bootstrap/app.php
   ```

3. **Clear caches**:
   ```bash
   php -r "opcache_reset();"
   ```

## Support

For deployment issues:
1. Check error logs: `/var/log/php_errors.log` or application logs
2. Verify file permissions
3. Test in staging environment first
4. Monitor application after deployment

## Next Steps

After successful deployment:

1. Monitor application performance
2. Set up log monitoring
3. Configure backup schedules
4. Regular security updates
5. Document any custom configurations

---

**Important**: Test each phase thoroughly before proceeding to the next phase. Always have backups before making production changes.
