#!/bin/bash

# BGAofis Hukuk Otomasyon - Backend Deployment Script
# This script handles backend deployment to name.com hosting

set -e  # Exit on any error

echo "🚀 BGAofis Backend Deployment Started"
echo "======================================"

# Configuration
SERVER=${FTP_SERVER:-"ftp.sistemyapielemanlari.com"}
USERNAME=${FTP_USERNAME:-"haslim@sistemyapielemanlari.com"}
PASSWORD=${FTP_PASSWORD:-"Fener1907****"}
SERVER_DIR=${SERVER_DIR:-"/public_html/bgaofis.billurguleraslim.av.tr/backend/"}
LOCAL_DIR="./backend/"

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
    
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed."
        exit 1
    fi
    
    print_status "Dependencies check completed ✓"
}

# Install backend dependencies
install_dependencies() {
    print_status "Installing backend dependencies..."
    cd backend
    
    if [ ! -f "composer.json" ]; then
        print_error "composer.json not found in backend directory"
        exit 1
    fi
    
    # Install composer dependencies if vendor directory doesn't exist
    if [ ! -d "vendor" ]; then
        print_status "Running composer install..."
        composer install --no-dev --optimize-autoloader
    else
        print_status "Vendor directory already exists, skipping composer install"
    fi
    
    cd ..
    print_status "Dependencies installed ✓"
}

# Prepare deployment files
prepare_files() {
    print_status "Preparing deployment files..."
    
    # Create necessary directories if they don't exist
    mkdir -p backend/logs
    mkdir -p backend/uploads
    mkdir -p backend/backups
    mkdir -p backend/temp
    
    # Set proper permissions
    chmod 755 backend/logs
    chmod 755 backend/uploads
    chmod 755 backend/backups
    chmod 755 backend/temp
    
    print_status "Files prepared ✓"
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
        --exclude "**/.git* **/node_modules/** **/tests/** **/.env* **/composer.lock" \
        --log-level "standard"
    
    print_status "Files deployed ✓"
}

# Run post-deployment setup
post_deployment() {
    print_status "Running post-deployment setup..."
    
    # Create a deployment info file
    cat > backend/deployment-info.json << EOF
{
    "deployment_date": "$(date -u +"%Y-%m-%dT%H:%M:%S.%3NZ")",
    "deployed_by": "automated-script",
    "version": "1.0.0",
    "environment": "production"
}
EOF
    
    print_status "Post-deployment setup completed ✓"
}

# Main deployment flow
main() {
    print_status "Starting backend deployment process..."
    
    check_dependencies
    install_dependencies
    prepare_files
    deploy_files
    post_deployment
    
    echo ""
    echo "🎉 Backend deployment completed successfully!"
    echo "📁 Deployed to: $SERVER_DIR"
    echo "🌐 Don't forget to:"
    echo "   1. Update your .env file on the server"
    echo "   2. Run database migrations: php database/migrate.php"
    echo "   3. Check file permissions"
    echo ""
}

# Run main function
main "$@"