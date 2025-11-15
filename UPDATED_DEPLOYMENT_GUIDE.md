# BGAofis Law Office Automation - Complete Deployment Guide

## 🚨 CRITICAL ISSUES IDENTIFIED AND FIXED

### Issue 1: Database Table Mismatch (RESOLVED)
**Problem**: The `FinanceTransaction` model was configured to use `finance_transactions` table, but the actual database table is `cash_transactions`.

**Solution**: Updated the model to point to the correct table name.

### Issue 2: Missing Permissions (RESOLVED)
**Problem**: The `/api/cases` endpoint requires `CASE_VIEW_ALL` permission, but this permission and others were missing from the database.

**Solution**: Created comprehensive permission system with all required permissions.

## 📋 COMPLETE FIXES LIST

### 1. Database Schema Fixes
- ✅ Fixed `cash_transactions` table structure (added missing `deleted_at` column)
- ✅ Created missing `workflow_templates` table
- ✅ Fixed `notifications` table structure (added missing `status` and `deleted_at` columns)
- ✅ Fixed `clients` table structure (added missing `deleted_at` column)

### 2. Model Fixes
- ✅ **CRITICAL**: Updated `FinanceTransaction` model to use `cash_transactions` table instead of `finance_transactions`

### 3. API Route Fixes
- ✅ Added missing routes for `/api/roles`
- ✅ Added missing routes for `/api/calendar/events`
- ✅ Added missing routes for `/api/finance/cash-stats`
- ✅ Added missing routes for `/api/finance/cash-transactions`
- ✅ Added missing routes for `/api/workflows/templates`

### 4. Controller Fixes
- ✅ Created new `CalendarController.php` with all required methods
- ✅ Updated `UserController.php` with missing `getRoles()` method
- ✅ Updated `FinanceController.php` with missing cash management methods

### 5. Permission System Fixes
- ✅ Created all required permissions (CASE_VIEW_ALL, CASH_VIEW, etc.)
- ✅ Ensured admin role exists and has all permissions
- ✅ Assigned admin role to existing users

## 🚀 IMMEDIATE DEPLOYMENT STEPS

### Step 1: Upload Fixed Files to Production

Upload these files to your production server:

#### Model Fix (CRITICAL)
```
backend/app/Models/FinanceTransaction.php
```

#### Controllers
```
backend/app/Controllers/CalendarController.php (NEW FILE)
backend/app/Controllers/UserController.php (UPDATED)
backend/app/Controllers/FinanceController.php (UPDATED)
```

#### Routes
```
backend/routes/api.php (UPDATED - use our complete-fix-deployment.php)
```

#### Permission Scripts
```
check-permissions.php (NEW)
fix-permissions.php (NEW)
```

### Step 2: Run Database Migration

Execute this script on your production server:
```bash
cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/
php simple-migration-runner.php
```

### Step 3: Fix Permissions

Execute the permission fix script:
```bash
cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/
php fix-permissions.php
```

### Step 4: Verify Permissions (Optional)

Check the current permission setup:
```bash
php check-permissions.php
```

### Step 5: Test API Endpoints

Test these endpoints manually:
```bash
# Dashboard (should work now)
curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard"

# Cases endpoint (should work now with permissions)
curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/cases"

# Roles endpoint
curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/roles"

# Calendar events
curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/calendar/events"

# Finance cash stats
curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-stats"
```

### Step 6: Test Frontend Application

1. Open your browser: https://bgaofis.billurguleraslim.av.tr
2. Check browser console for errors
3. Test all application features:
   - Dashboard loading
   - Cases access
   - Calendar functionality
   - Finance/cash management
   - User role management
   - Workflow templates

## 🔍 EXPECTED RESULTS

### Before Fixes
```
❌ GET /api/dashboard - 500 Internal Server Error
❌ GET /api/cases - 403 Forbidden
❌ GET /api/roles - 405 Method Not Allowed
❌ GET /api/calendar/events - 405 Method Not Allowed
❌ GET /api/finance/cash-stats - 405 Method Not Allowed
```

### After Fixes
```
✅ GET /api/dashboard - 200 OK (returns dashboard data)
✅ GET /api/cases - 200 OK (returns cases data)
✅ GET /api/roles - 200 OK (returns roles list)
✅ GET /api/calendar/events - 200 OK (returns calendar events)
✅ GET /api/finance/cash-stats - 200 OK (returns cash statistics)
```

## 🛠️ TROUBLESHOOTING

### If Issues Persist After Deployment

1. **Check File Permissions**
   ```bash
   chmod 644 backend/app/Models/FinanceTransaction.php
   chmod 644 backend/app/Controllers/*.php
   chmod 644 backend/routes/api.php
   ```

2. **Clear PHP Cache**
   ```bash
   # Clear OPcache if enabled
   php -r "opcache_reset();"
   ```

3. **Check Error Logs**
   - cPanel → Metrics → Errors
   - Look for recent PHP errors

4. **Verify Database Changes**
   ```sql
   -- Check if tables exist
   SHOW TABLES LIKE 'cash_transactions';
   SHOW TABLES LIKE 'workflow_templates';
   
   -- Check if permissions exist
   SELECT * FROM permissions WHERE `key` = 'CASE_VIEW_ALL';
   
   -- Check if columns exist
   DESCRIBE cash_transactions;
   DESCRIBE notifications;
   ```

5. **Verify User Authentication**
   - Ensure user is logged in
   - Check session is valid
   - Verify user has admin role

## 📁 FILES TO DEPLOY

### Critical Files (Must Deploy)
1. `backend/app/Models/FinanceTransaction.php` - **FIXES TABLE NAME**
2. `backend/app/Controllers/CalendarController.php` - **NEW FILE**
3. `backend/app/Controllers/UserController.php` - **UPDATED**
4. `backend/app/Controllers/FinanceController.php` - **UPDATED**
5. `backend/routes/api.php` - **UPDATED**

### Database & Permission Scripts
6. `simple-migration-runner.php` - **Run once on server**
7. `fix-permissions.php` - **Run once on server**
8. `check-permissions.php` - **Optional verification**

## 🎯 SUCCESS CRITERIA

Your application is fully working when:
- ✅ Dashboard loads without errors
- ✅ Cases page loads without 403 errors
- ✅ All API endpoints return 200 OK responses
- ✅ No JavaScript errors in browser console
- ✅ All application features work correctly
- ✅ No 500, 403, or 405 errors in network tab

## 📊 PERMISSION SYSTEM

The fix creates these permissions:
- `CASE_VIEW_ALL` - View all cases
- `CASE_CREATE` - Create new cases
- `CASE_EDIT` - Edit cases
- `CASE_DELETE` - Delete cases
- `CLIENT_VIEW_ALL` - View all clients
- `CASH_VIEW` - View cash flow
- `CASH_MANAGE` - Manage cash transactions
- `USER_MANAGE` - Manage users
- `ROLE_MANAGE` - Manage roles
- `DOCUMENT_VIEW` - View documents
- `DOCUMENT_MANAGE` - Manage documents
- `TASK_MANAGE` - Manage tasks
- `WORKFLOW_MANAGE` - Manage workflows

All permissions are automatically assigned to the admin role.

## 📞 SUPPORT

If you need further assistance:
1. Deploy all the files listed above
2. Run the migration and permission scripts
3. Test the application
4. If issues persist, provide the exact error messages from:
   - Browser console
   - Network tab (failed requests)
   - Server error logs
   - Permission check script output

---

**Last Updated**: 2025-11-15  
**Status**: Ready for Deployment  
**Priority**: CRITICAL - Fixes production 500 and 403 errors