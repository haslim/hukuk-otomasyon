#!/bin/bash

# BGAofis Hukuk Otomasyon - Deployment Test Script
# This script validates the deployment configuration

set -e  # Exit on any error

echo "🧪 BGAofis Deployment Test Suite"
echo "================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test counters
TESTS_PASSED=0
TESTS_FAILED=0

# Function to print colored output
print_status() {
    echo -e "${GREEN}[PASS]${NC} $1"
    ((TESTS_PASSED++))
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[FAIL]${NC} $1"
    ((TESTS_FAILED++))
}

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Test 1: Check if required files exist
test_file_structure() {
    print_info "Testing file structure..."
    
    if [ -f "README.md" ]; then
        print_status "README.md exists"
    else
        print_error "README.md missing"
    fi
    
    if [ -f ".gitignore" ]; then
        print_status ".gitignore exists"
    else
        print_error ".gitignore missing"
    fi
    
    if [ -f "deploy-backend.sh" ]; then
        print_status "deploy-backend.sh exists"
    else
        print_error "deploy-backend.sh missing"
    fi
    
    if [ -f "deploy-frontend.sh" ]; then
        print_status "deploy-frontend.sh exists"
    else
        print_error "deploy-frontend.sh missing"
    fi
    
    if [ -f ".github/workflows/deploy.yml" ]; then
        print_status "GitHub Actions workflow exists"
    else
        print_error "GitHub Actions workflow missing"
    fi
    
    if [ -f "webhook-handler.php" ]; then
        print_status "Webhook handler exists"
    else
        print_error "Webhook handler missing"
    fi
}

# Test 2: Check backend structure
test_backend_structure() {
    print_info "Testing backend structure..."
    
    if [ -d "backend" ]; then
        print_status "Backend directory exists"
    else
        print_error "Backend directory missing"
        return
    fi
    
    if [ -f "backend/composer.json" ]; then
        print_status "composer.json exists"
    else
        print_error "composer.json missing"
    fi
    
    if [ -f "backend/deploy.php" ]; then
        print_status "Backend deployment script exists"
    else
        print_error "Backend deployment script missing"
    fi
    
    if [ -f "backend/.env.example" ]; then
        print_status ".env.example exists"
    else
        print_error ".env.example missing"
    fi
    
    if [ -d "backend/database" ]; then
        print_status "Database directory exists"
    else
        print_error "Database directory missing"
    fi
    
    if [ -f "backend/database/migrate.php" ]; then
        print_status "Migration script exists"
    else
        print_error "Migration script missing"
    fi
}

# Test 3: Check frontend structure
test_frontend_structure() {
    print_info "Testing frontend structure..."
    
    if [ -d "frontend" ]; then
        print_status "Frontend directory exists"
    else
        print_error "Frontend directory missing"
        return
    fi
    
    if [ -f "frontend/package.json" ]; then
        print_status "package.json exists"
    else
        print_error "package.json missing"
    fi
    
    if [ -f "frontend/vite.config.ts" ]; then
        print_status "Vite config exists"
    else
        print_error "Vite config missing"
    fi
    
    if [ -f "frontend/tsconfig.json" ]; then
        print_status "TypeScript config exists"
    else
        print_error "TypeScript config missing"
    fi
    
    if [ -f "frontend/.env.example" ]; then
        print_status "Frontend .env.example exists"
    else
        print_error "Frontend .env.example missing"
    fi
}

# Test 4: Check script permissions
test_script_permissions() {
    print_info "Testing script permissions..."
    
    if [ -x "deploy-backend.sh" ]; then
        print_status "deploy-backend.sh is executable"
    else
        print_warning "deploy-backend.sh is not executable (run: chmod +x deploy-backend.sh)"
    fi
    
    if [ -x "deploy-frontend.sh" ]; then
        print_status "deploy-frontend.sh is executable"
    else
        print_warning "deploy-frontend.sh is not executable (run: chmod +x deploy-frontend.sh)"
    fi
    
    if [ -x "scripts/create-htaccess.sh" ]; then
        print_status "create-htaccess.sh is executable"
    else
        print_warning "create-htaccess.sh is not executable (run: chmod +x scripts/create-htaccess.sh)"
    fi
}

# Test 5: Check .gitignore patterns
test_gitignore() {
    print_info "Testing .gitignore patterns..."
    
    if grep -q "node_modules" .gitignore; then
        print_status "node_modules ignored"
    else
        print_error "node_modules not ignored"
    fi
    
    if grep -q "vendor" .gitignore; then
        print_status "vendor ignored"
    else
        print_error "vendor not ignored"
    fi
    
    if grep -q ".env" .gitignore; then
        print_status ".env files ignored"
    else
        print_error ".env files not ignored"
    fi
    
    if grep -q "dist" .gitignore; then
        print_status "dist files ignored"
    else
        print_error "dist files not ignored"
    fi
}

# Test 6: Check GitHub Actions workflow
test_github_workflow() {
    print_info "Testing GitHub Actions workflow..."
    
    if grep -q "name:" .github/workflows/deploy.yml; then
        print_status "Workflow has name"
    else
        print_error "Workflow name missing"
    fi
    
    if grep -q "on:" .github/workflows/deploy.yml; then
        print_status "Workflow has triggers"
    else
        print_error "Workflow triggers missing"
    fi
    
    if grep -q "deploy-backend" .github/workflows/deploy.yml; then
        print_status "Backend deployment job exists"
    else
        print_error "Backend deployment job missing"
    fi
    
    if grep -q "deploy-frontend" .github/workflows/deploy.yml; then
        print_status "Frontend deployment job exists"
    else
        print_error "Frontend deployment job missing"
    fi
}

# Test 7: Check webhook handler
test_webhook_handler() {
    print_info "Testing webhook handler..."
    
    if grep -q "webhook-handler.php" webhook-handler.php; then
        print_status "Webhook handler has proper filename reference"
    else
        print_warning "Webhook handler filename check"
    fi
    
    if grep -q "verifySignature" webhook-handler.php; then
        print_status "Webhook signature verification exists"
    else
        print_error "Webhook signature verification missing"
    fi
    
    if grep -q "git pull" webhook-handler.php; then
        print_status "Git pull command exists"
    else
        print_error "Git pull command missing"
    fi
}

# Test 8: Check documentation
test_documentation() {
    print_info "Testing documentation..."
    
    if [ -f "NAMECOM_GIT_DEPLOYMENT_GUIDE.md" ]; then
        print_status "name.com deployment guide exists"
    else
        print_error "name.com deployment guide missing"
    fi
    
    if grep -q "name.com" NAMECOM_GIT_DEPLOYMENT_GUIDE.md; then
        print_status "Guide contains name.com references"
    else
        print_error "Guide missing name.com references"
    fi
}

# Test 9: Simulate deployment process
test_deployment_simulation() {
    print_info "Testing deployment simulation..."
    
    # Test backend composer validation
    if [ -f "backend/composer.json" ]; then
        cd backend
        if composer validate --no-check-all 2>/dev/null; then
            print_status "composer.json is valid"
        else
            print_error "composer.json is invalid"
        fi
        cd ..
    fi
    
    # Test frontend package.json validation
    if [ -f "frontend/package.json" ]; then
        cd frontend
        if npm ls --depth=0 >/dev/null 2>&1; then
            print_status "package.json is valid"
        else
            print_error "package.json is invalid"
        fi
        cd ..
    fi
}

# Test 10: Check environment variables template
test_env_template() {
    print_info "Testing environment templates..."
    
    if [ -f "backend/.env.example" ]; then
        if grep -q "DB_" backend/.env.example; then
            print_status "Backend .env.example has database variables"
        else
            print_error "Backend .env.example missing database variables"
        fi
        
        if grep -q "APP_" backend/.env.example; then
            print_status "Backend .env.example has app variables"
        else
            print_error "Backend .env.example missing app variables"
        fi
    fi
    
    if [ -f "frontend/.env.example" ]; then
        if grep -q "VITE_" frontend/.env.example; then
            print_status "Frontend .env.example has Vite variables"
        else
            print_error "Frontend .env.example missing Vite variables"
        fi
    fi
}

# Main test execution
main() {
    echo "Running deployment configuration tests..."
    echo ""
    
    test_file_structure
    echo ""
    
    test_backend_structure
    echo ""
    
    test_frontend_structure
    echo ""
    
    test_script_permissions
    echo ""
    
    test_gitignore
    echo ""
    
    test_github_workflow
    echo ""
    
    test_webhook_handler
    echo ""
    
    test_documentation
    echo ""
    
    test_deployment_simulation
    echo ""
    
    test_env_template
    echo ""
    
    # Print summary
    echo "================================="
    echo "🧪 Test Results Summary"
    echo "================================="
    echo -e "${GREEN}Tests Passed: ${TESTS_PASSED}${NC}"
    echo -e "${RED}Tests Failed: ${TESTS_FAILED}${NC}"
    echo -e "${BLUE}Total Tests: $((TESTS_PASSED + TESTS_FAILED))${NC}"
    echo ""
    
    if [ $TESTS_FAILED -eq 0 ]; then
        echo -e "${GREEN}🎉 All tests passed! Your deployment configuration is ready.${NC}"
        echo ""
        echo "📝 Next steps:"
        echo "1. Set up your GitHub repository"
        echo "2. Configure GitHub secrets"
        echo "3. Set up name.com hosting"
        echo "4. Configure webhooks"
        echo "5. Push to main/master branch"
        echo ""
        exit 0
    else
        echo -e "${RED}❌ Some tests failed. Please fix the issues above.${NC}"
        echo ""
        echo "🔧 Common fixes:"
        echo "1. Make scripts executable: chmod +x *.sh scripts/*.sh"
        echo "2. Create missing files or directories"
        echo "3. Fix configuration files"
        echo "4. Check file permissions"
        echo ""
        exit 1
    fi
}

# Run main function
main "$@"