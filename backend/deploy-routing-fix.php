<?php
// Deploy routing fix to production
echo "=== DEPLOYING ROUTING FIX ===\n\n";

// Step 1: Create a comprehensive .htaccess file
echo "1. Creating comprehensive .htaccess file...\n";

$htaccessContent = '# Main domain .htaccess for BGAofis Law Office Automation
# Updated: ' . date('Y-m-d H:i:s') . '

# Enable rewrite engine
RewriteEngine On

# Force HTTPS for all requests
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# All API routes to backend (MUST COME FIRST)
RewriteCond %{REQUEST_URI} ^/api/ [NC]
RewriteRule ^api/(.*)$ backend/public/index.php [QSA,L]

# Handle OPTIONS preflight requests for CORS
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ backend/public/index.php [QSA,L]

# All other requests go to frontend (index.html)
RewriteCond %{REQUEST_URI} !^/api/ [NC]
RewriteCond %{REQUEST_URI} !^/backend/ [NC]
RewriteRule ^(.*)$ frontend/index.html [L]

# Set proper headers for all responses
<IfModule mod_headers.c>
    # CORS headers
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, PATCH, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, X-File-Name, Cache-Control"
    Header always set Access-Control-Allow-Credentials "true"
    Header always set Access-Control-Max-Age "86400"
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Remove server signature
    Header always unset Server
    Header always unset X-Powered-By
</IfModule>

# Block access to sensitive files and directories
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^(composer|package|\.env|\.git)">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.(env|log|md|txt)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Set proper directory index
<IfModule mod_dir.c>
    DirectoryIndex index.html index.php
</IfModule>

# MIME types for frontend assets
<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType text/css .css
    AddType image/svg+xml .svg
    AddType image/png .png
    AddType image/jpeg .jpg .jpeg
    AddType image/gif .gif
    AddType image/x-icon .ico
    AddType font/woff .woff
    AddType font/woff2 .woff2
    AddType font/ttf .ttf
    AddType font/eot .eot
    AddType application/json .json
</IfModule>

# Compression for better performance
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
    AddOutputFilterByType DEFLATE application/json
</IfModule>

# Cache control for static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/svg "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType font/ttf "access plus 1 year"
    ExpiresByType font/eot "access plus 1 year"
    ExpiresByType application/json "access plus 0 seconds"
</IfModule>

# Error handling
ErrorDocument 404 /index.html
ErrorDocument 500 /index.html

# PHP settings (if PHP is used directly)
<IfModule mod_php8.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log /tmp/php_errors.log
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
</IfModule>
';

// Write the new .htaccess file
if (file_put_contents(__DIR__ . '/../.htaccess', $htaccessContent)) {
    echo "   ✓ Root .htaccess file updated successfully\n";
} else {
    echo "   ✗ Failed to update root .htaccess file\n";
}

// Step 2: Ensure backend .htaccess is correct
echo "\n2. Updating backend .htaccess file...\n";

$backendHtaccess = '# Backend .htaccess for BGAofis Law Office Automation
# Updated: ' . date('Y-m-d H:i:s') . '

RewriteEngine On

# Redirect all requests to the public directory
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L]

# Handle API routes directly through index.php
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^(.*)$ public/index.php [QSA,L]
';

if (file_put_contents(__DIR__ . '/.htaccess', $backendHtaccess)) {
    echo "   ✓ Backend .htaccess file updated successfully\n";
} else {
    echo "   ✗ Failed to update backend .htaccess file\n";
}

// Step 3: Ensure public .htaccess is correct
echo "\n3. Updating backend/public/.htaccess file...\n";

$publicHtaccess = '# Public directory .htaccess for BGAofis API
# Updated: ' . date('Y-m-d H:i:s') . '

# Enable rewrite engine
RewriteEngine On

# Preserve Authorization headers for PHP
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

# Set the default directory index
DirectoryIndex index.php

# Redirect requests to index.php if the requested file doesn\'t exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Ensure API routes are properly handled
RewriteCond %{REQUEST_METHOD} ^(GET|POST|PUT|DELETE|PATCH|OPTIONS)$
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ index.php [QSA,L]

# Set proper headers for API responses
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, PATCH, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, X-File-Name, Cache-Control"
    Header always set Access-Control-Allow-Credentials "true"
    Header always set Access-Control-Max-Age "86400"
</IfModule>

# PHP configuration for production
<IfModule mod_php8.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log ../logs/error.log
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Block access to sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^(composer|package|\.env)\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Set proper MIME types
<IfModule mod_mime.c>
    AddType application/json .json
    AddType text/css .css
    AddType application/javascript .js
    AddType text/javascript .js
    AddType image/svg+xml .svg
    AddType image/png .png
    AddType image/jpeg .jpg .jpeg
    AddType image/gif .gif
    AddType image/x-icon .ico
</IfModule>
';

if (file_put_contents(__DIR__ . '/public/.htaccess', $publicHtaccess)) {
    echo "   ✓ Public .htaccess file updated successfully\n";
} else {
    echo "   ✗ Failed to update public .htaccess file\n";
}

// Step 4: Create a test endpoint to verify routing
echo "\n4. Creating test endpoint...\n";

$testEndpoint = '<?php
// Test endpoint to verify routing is working
header("Content-Type: application/json");
echo json_encode([
    "message" => "Routing is working correctly!",
    "timestamp" => time(),
    "method" => $_SERVER["REQUEST_METHOD"],
    "uri" => $_SERVER["REQUEST_URI"],
    "headers" => getallheaders()
]);
?>';

if (file_put_contents(__DIR__ . '/public/test-routing.php', $testEndpoint)) {
    echo "   ✓ Test endpoint created\n";
} else {
    echo "   ✗ Failed to create test endpoint\n";
}

echo "\n=== ROUTING FIX DEPLOYMENT COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. Upload these files to the production server\n";
echo "2. Clear any server cache (if applicable)\n";
echo "3. Restart the web server (if possible)\n";
echo "4. Test the API endpoints\n";
echo "5. If still not working, check server configuration for mod_rewrite\n";
?>