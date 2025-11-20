# CORS Fix for 405 Method Not Allowed Errors

## Problem Analysis

The frontend was experiencing 405 Method Not Allowed errors for the following API endpoints:
- `GET /api/notifications`
- `GET /api/menu/my` 
- `GET /api/dashboard`

The error message indicated "Method not allowed. Must be one of: OPTIONS", which suggested a CORS preflight issue rather than a routing problem.

## Root Cause

The issue was caused by incomplete CORS configuration in the Slim application. The existing CORS middleware was not properly handling preflight requests and was missing some essential headers needed for modern browser applications.

## Solution Applied

### 1. Enhanced CORS Middleware

Updated `backend/bootstrap/app.php` with comprehensive CORS handling:

```php
// Enhanced CORS handling for all requests
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    // Get allowed origin from environment or allow all for development
    $allowedOrigin = $_ENV['CORS_ORIGIN'] ?? '*';
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Cache-Control, X-File-Name')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Max-Age', '86400'); // 24 hours
});
```

### 2. Improved OPTIONS Handling

Added proper OPTIONS request handler:

```php
// Better OPTIONS handling
$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    return $response
        ->withStatus(200)
        ->withHeader('Access-Control-Allow-Origin', $_ENV['CORS_ORIGIN'] ?? '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Cache-Control, X-File-Name')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Max-Age', '86400')
        ->withHeader('Content-Length', '0');
});
```

## Key Improvements

1. **Comprehensive Headers**: Added support for `Cache-Control` and `X-File-Name` headers
2. **Preflight Optimization**: Added `Access-Control-Max-Age` to reduce preflight requests
3. **Proper OPTIONS Response**: Returns 200 status with correct CORS headers
4. **Credentials Support**: Enabled `Access-Control-Allow-Credentials` for authenticated requests
5. **Environment Configuration**: Uses `CORS_ORIGIN` environment variable for production flexibility

## Files Modified

- `backend/bootstrap/app.php` - Enhanced CORS configuration
- Backup created: `backend/bootstrap/app.php.backup.2025-11-20-06-49-12`

## Deployment Instructions

### To Production Server

1. **Upload Files**: Copy the updated `backend/bootstrap/app.php` to your production server
2. **Clear Cache**: Clear any opcode cache (opcache, APC, etc.)
3. **Restart Server**: Restart Apache/Nginx if needed
4. **Test Application**: Verify frontend works correctly

### Testing

The following test scripts are available:

- `backend/simple-cors-test.php` - Verifies CORS configuration
- `backend/test-api-routes.php` - Lists all registered API routes

## Verification Results

✅ All CORS enhancements successfully applied:
- Enhanced CORS middleware
- Cache-Control header support  
- X-File-Name header support
- Access-Control-Max-Age header
- Proper OPTIONS handling
- CORS credentials support

✅ All problematic API routes properly configured:
- GET /api/dashboard
- GET /api/notifications  
- GET /api/menu/my

## Expected Outcome

After deployment, the 405 Method Not Allowed errors should be resolved. The frontend should be able to successfully make GET requests to:
- `/api/notifications`
- `/api/menu/my`
- `/api/dashboard`

## Troubleshooting

If issues persist after deployment:

1. **Check Server Configuration**: Ensure Apache/Nginx allows OPTIONS requests
2. **Verify Headers**: Use browser developer tools to inspect response headers
3. **Clear Browser Cache**: Clear browser cache and hard refresh
4. **Check Environment**: Verify `CORS_ORIGIN` environment variable if set

## Security Notes

- The current configuration allows all origins (`*`) for development
- For production, set `CORS_ORIGIN` environment variable to your frontend domain
- Credentials are enabled for authenticated API requests
- All standard HTTP methods are supported for API functionality
