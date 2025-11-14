#!/bin/bash

# BGAofis Hukuk Otomasyon - Frontend Deployment Script
# This script handles frontend deployment to name.com hosting

set -e  # Exit on any error

echo "🚀 BGAofis Frontend Deployment Started"
echo "======================================="

# Configuration
SERVER=${FTP_SERVER:-"ftp.bgaofis.billurguleraslim.av.tr"}
USERNAME=${FTP_USERNAME:-"haslim@bgaofis.billurguleraslim.av.tr"}
PASSWORD=${FTP_PASSWORD:-"Fener1907****"}
SERVER_DIR=${SERVER_DIR:-"/public_html/bgaofis.billurguleraslim.av.tr/"}
LOCAL_DIR="./frontend/dist/"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if required tools are installed
check_dependencies() {
    print_status "Checking dependencies..."
    
    if ! command -v npm &> /dev/null; then
        print_error "npm is not installed. Please install Node.js and npm."
        exit 1
    fi
    
    if ! command -v node &> /dev/null; then
        print_error "Node.js is not installed."
        exit 1
    fi
    
    print_status "Dependencies check completed ✓"
}

# Install frontend dependencies
install_dependencies() {
    print_status "Installing frontend dependencies..."
    cd frontend
    
    if [ ! -f "package.json" ]; then
        print_error "package.json not found in frontend directory"
        exit 1
    fi
    
    # Install npm dependencies
    print_status "Running npm install..."
    npm install
    
    cd ..
    print_status "Dependencies installed ✓"
}

# Build frontend application
build_frontend() {
    print_status "Building frontend application..."
    cd frontend
    
    # Check if .env file exists, copy from example if not
    if [ ! -f ".env" ] && [ -f ".env.example" ]; then
        print_warning ".env file not found, copying from .env.example"
        cp .env.example .env
        print_warning "Please update .env file with your production values"
    fi
    
    # Build for production
    print_status "Running npm run build..."
    npm run build
    
    # Check if build was successful
    if [ ! -d "dist" ]; then
        print_error "Build failed - dist directory not created"
        exit 1
    fi
    
    cd ..
    print_status "Frontend build completed ✓"
}

# Optimize build files
optimize_build() {
    print_status "Optimizing build files..."
    
    # Create a .htaccess file for the frontend if it doesn't exist
    if [ ! -f "frontend/dist/.htaccess" ]; then
        cat > frontend/dist/.htaccess << 'EOF'
# Frontend .htaccess for React Router
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
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

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 month"
    ExpiresByType image/icon "access plus 1 month"
    ExpiresByType text/html "access plus 1 hour"
</IfModule>
EOF
        print_status "Created .htaccess file for frontend ✓"
    fi
    
    print_status "Build optimization completed ✓"
}

# Deploy using ftp-deploy
deploy_files() {
    print_status "Starting FTP deployment..."
    
    # Check if ftp-deploy is installed
    if ! command -v ftp-deploy &> /dev/null; then
        print_status "Installing ftp-deploy..."
        npm install -g @samkirkland/ftp-deploy
    fi
    
    # Deploy files
    ftp-deploy \
        --server "$SERVER" \
        --username "$USERNAME" \
        --password "$PASSWORD" \
        --local-dir "$LOCAL_DIR" \
        --server-dir "$SERVER_DIR" \
        --exclude "**/node_modules/** **/src/** **/.git* **/package*.json **/tsconfig*.json **/vite.config.*" \
        --log-level "standard"
    
    print_status "Files deployed ✓"
}

# Run post-deployment setup
post_deployment() {
    print_status "Running post-deployment setup..."
    
    # Create a deployment info file
    cat > frontend/dist/deployment-info.json << EOF
{
    "deployment_date": "$(date -u +"%Y-%m-%dT%H:%M:%S.%3NZ")",
    "deployed_by": "automated-script",
    "version": "1.0.0",
    "environment": "production",
    "build_tool": "Vite",
    "framework": "React"
}
EOF
    
    print_status "Post-deployment setup completed ✓"
}

# Main deployment flow
main() {
    print_status "Starting frontend deployment process..."
    
    check_dependencies
    install_dependencies
    build_frontend
    optimize_build
    deploy_files
    post_deployment
    
    echo ""
    echo "🎉 Frontend deployment completed successfully!"
    echo "📁 Deployed to: $SERVER_DIR"
    echo "🌐 Your application should be available at: https://yourdomain.com"
    echo ""
    echo "📝 Next steps:"
    echo "   1. Update your API base URL in the frontend if needed"
    echo "   2. Test the application in browser"
    echo "   3. Check browser console for any errors"
    echo ""
}

# Run main function
main "$@"