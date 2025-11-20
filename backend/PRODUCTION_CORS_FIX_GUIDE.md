# Production CORS Fix Deployment Guide

## Problem
The frontend is getting 405 Method Not Allowed errors when trying to access API endpoints:
- `/api/dashboard`
- `/api/notifications` 
- `/api/menu/my`

The error message shows: "Method not allowed. Must be one of: OPTIONS"

## Root Cause
The OPTIONS handler for CORS preflight requests was being registered AFTER the routing middleware, causing OPTIONS requests to be processed by the routing system instead of being handled by the CORS middleware.

## Solution
Move the OPTIONS handler to be registered BEFORE the routing middleware in `bootstrap/app.php`.

## Files Modified
1. `backend/bootstrap/app.php` - Fixed CORS middleware order
2. `backend/deploy-cors-fix-production.php` - Production deployment script
3. `backend/deploy-cors-fix.php` - Local testing script (already fixed)

## Deployment Steps

### Method 1: Using the Deployment Script (Recommended)

1. **Upload the fixed files to production server**
2. **Run the deployment script**:
   ```bash
   cd /path/to/backend
   php deploy-cors-fix-production.php
   ```

### Method 2: Manual File Replacement

1. **Backup the current production file**:
   ```bash
   cp bootstrap/app.php bootstrap/app.php.backup.$(date +%Y-%m-%d-%H-%M-%S)
   ```

2. **Replace bootstrap/app.php** with the fixed version from this repository

3. **Clear opcode cache** (if using OPcache):
   ```bash
   php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'Opcode cache cleared\n'; }"
   ```

### Method 3: Manual Code Changes

Edit `bootstrap/app.php` and move the OPTIONS handler to be BEFORE the routing middleware:

**BEFORE (Incorrect):**
```php
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
// ... CORS middleware ...
$app->options('/{routes:.+}', function (...) { ... }); // AFTER routing
```

**AFTER (Correct):**
```php
$app = AppFactory::create();
$app->options('/{routes:.+}', function (...) { ... }); // BEFORE routing
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
// ... CORS middleware ...
```

## Verification Steps

1. **Test the deployment script locally first**:
   ```bash
   php deploy-cors-fix.php
   ```

2. **Check that routes are properly registered**:
   ```bash
   php test-api-routes.php
   ```

3. **Test API endpoints directly**:
   ```bash
   curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard" \
        -H "Authorization: Bearer YOUR_TOKEN" \
        -H "Content-Type: application/json"
   ```

4. **Test OPTIONS preflight requests**:
   ```bash
   curl -X OPTIONS "https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard" \
        -H "Origin: https://your-frontend-domain.com" \
        -H "Access-Control-Request-Method: GET" \
        -H "Access-Control-Request-Headers: Authorization,Content-Type"
   ```

## Expected Results

After applying the fix:

1. **OPTIONS requests** should return HTTP 200 with proper CORS headers
2. **GET requests** should work normally for authenticated users
3. **Frontend should no longer show 405 Method Not Allowed errors**

## Troubleshooting

### If 405 errors persist:

1. **Check web server configuration** (Apache/Nginx)
2. **Verify the fix was applied correctly**:
   ```bash
   grep -n "options.*routes" bootstrap/app.php
   ```
3. **Check for caching**:
   - Clear browser cache
   - Clear server cache
   - Restart web server if needed

### If authentication errors occur:

1. **Verify JWT token is valid**
2. **Check AuthMiddleware configuration**
3. **Ensure user has proper permissions**

## rollback

If something goes wrong, restore from backup:

```bash
cp bootstrap/app.php.backup.YYYY-MM-DD-HH-MM-SS bootstrap/app.php
```

## Notes

- This fix only affects the order of middleware registration
- No database changes are required
- The fix is backward compatible
- Multiple backups are created automatically

## Contact

If you encounter issues with this deployment:
1. Check the error logs on the production server
2. Verify the file permissions
3. Ensure all required PHP extensions are installed
4. Test the fix in a staging environment first if possible
