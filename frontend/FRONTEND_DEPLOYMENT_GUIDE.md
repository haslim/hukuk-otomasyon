# Frontend Deployment Guide - BGAofis Hukuk Otomasyon

This guide provides comprehensive instructions for building and deploying the React frontend application to name.com hosting.

## Overview

The frontend is a React/TypeScript Single Page Application (SPA) built with:
- **Vite** as the build tool
- **React Router** for client-side routing
- **TailwindCSS** for styling
- **Axios** for API communication
- **Zustand** for state management

## Prerequisites

Before deploying, ensure you have:
- Node.js (v18 or higher) installed
- npm or yarn package manager
- Access to the name.com hosting control panel
- FTP/SFTP access to the web root directory

## 1. Production Build Process

### Step 1: Install Dependencies
```bash
cd frontend
npm install
```

### Step 2: Configure Environment Variables
Verify that your `.env` file contains the correct production configuration:

```env
# API Configuration (production)
VITE_API_URL=https://backend.bgaofis.billurguleraslim.av.tr/api

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

### Step 3: Build for Production
```bash
# Run the production build
npm run build:prod
```

This will:
- Compile TypeScript files
- Optimize and minify JavaScript/CSS
- Generate production assets in the `dist/` directory
- Remove console.log statements and debugger statements
- Create chunked bundles for better performance

### Step 4: Preview the Build (Optional)
```bash
npm run preview
```
This starts a local server to preview the production build at `http://localhost:4173`

## 2. Files to Upload

After building, upload the entire contents of the `dist/` directory to your web root. The directory structure should be:

```
web-root/
├── index.html
├── assets/
│   ├── index-[hash].css
│   ├── index-[hash].js
│   └── vendor-[hash].js
│   └── router-[hash].js
│   └── query-[hash].js
│   └── utils-[hash].js
└── .htaccess (create this file - see section 3)
```

## 3. Server Configuration

### Create .htaccess for SPA Routing

Create a `.htaccess` file in your web root with the following content to handle client-side routing:

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

## 4. API Configuration

The frontend is configured to communicate with the backend API at:
`https://bgaofis.billurguleraslim.av.tr/api`

### CORS Configuration
Ensure your backend has proper CORS headers configured to allow requests from your domain. The backend should include headers like:

```php
Access-Control-Allow-Origin: https://yourdomain.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

## 5. Deployment Checklist

### Pre-Deployment
- [ ] Environment variables are set correctly for production
- [ ] API endpoint is accessible and responding
- [ ] Build process completes without errors
- [ ] Test the production build locally using `npm run preview`

### Post-Deployment
- [ ] All files uploaded to web root
- [ ] `.htaccess` file is in place and working
- [ ] Application loads correctly in browser
- [ ] All routes are accessible (test direct URL navigation)
- [ ] API calls are working correctly
- [ ] Authentication flow is working
- [ ] File uploads are working (if applicable)

## 6. Troubleshooting

### Common Issues

#### 404 Errors on Page Refresh
**Problem**: Direct navigation to routes returns 404 errors
**Solution**: Ensure `.htaccess` file is properly configured and uploaded

#### API Connection Errors
**Problem**: Frontend cannot connect to backend API
**Solution**: 
1. Verify API URL in environment variables
2. Check CORS configuration on backend
3. Ensure HTTPS is properly configured

#### White Screen/Blank Page
**Problem**: Application loads but shows blank screen
**Solution**:
1. Check browser console for JavaScript errors
2. Verify all asset files are uploaded
3. Check MIME types in `.htaccess`

#### Styles Not Loading
**Problem**: Application loads but without styling
**Solution**:
1. Verify CSS files are uploaded
2. Check paths in `index.html`
3. Clear browser cache

## 7. Performance Optimization

The build process already includes:
- Code splitting for better loading performance
- Asset minification and compression
- Tree shaking to remove unused code
- Browser caching headers

### Additional Recommendations
- Consider implementing a CDN for static assets
- Enable HTTP/2 on your server if available
- Monitor bundle size and optimize as needed

## 8. Security Considerations

- All API communication should use HTTPS
- Sensitive data should not be stored in localStorage
- Implement proper authentication token handling
- Regular security updates for dependencies
- Consider implementing CSP headers

## 9. Maintenance

### Regular Tasks
- Update dependencies regularly: `npm update`
- Monitor for security vulnerabilities: `npm audit`
- Test new builds before deployment
- Keep backup of previous working version

### Deployment Commands Summary
```bash
# Install dependencies
npm install

# Build for production
npm run build:prod

# Preview production build
npm run preview

# Update dependencies
npm update

# Check for security issues
npm audit
```

## 10. Support

For deployment issues:
1. Check browser console for errors
2. Verify server configuration
3. Test API endpoints directly
4. Review this troubleshooting guide
5. Contact development team if issues persist
