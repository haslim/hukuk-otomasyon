#!/bin/bash

# Deployment script for route fix
# This script deploys the .htaccess fixes to resolve 405 Method Not Allowed errors

echo "=== BGAofis Route Fix Deployment ==="
echo "Deploying .htaccess fixes to resolve 405 Method Not Allowed errors..."
echo ""

# Backup original files
echo "Creating backups..."
cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S)
cp public/.htaccess public/.htaccess.backup.$(date +%Y%m%d_%H%M%S)
echo "✓ Backups created"

# Update permissions
echo "Setting proper permissions..."
chmod 644 .htaccess
chmod 644 public/.htaccess
echo "✓ Permissions set"

# Test API routes
echo ""
echo "Testing API routes..."
php test-real-api.php

echo ""
echo "=== Deployment Complete ==="
echo "The following changes were made:"
echo "1. Updated backend/.htaccess to properly handle API routes"
echo "2. Enhanced backend/public/.htaccess with better route handling"
echo "3. Added proper CORS headers and OPTIONS request handling"
echo ""
echo "API endpoints should now work correctly:"
echo "- /api/dashboard"
echo "- /api/notifications" 
echo "- /api/menu/my"
echo ""
echo "If you still experience issues, check:"
echo "1. Apache mod_rewrite is enabled"
echo "2. .htaccess files are allowed in Apache config"
echo "3. Proper directory permissions are set"
