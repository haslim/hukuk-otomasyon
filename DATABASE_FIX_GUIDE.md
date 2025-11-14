# BGAofis Law Office Automation - Database Migration Fix Guide

## Problem Summary
Your application is experiencing a **500 Internal Server Error** when accessing the dashboard API endpoint. The root cause is that the `finance_transactions` table (and potentially other tables) don't exist in your production database because the database migrations haven't been run.

## Quick Fix Instructions

### Option 1: Using the Automated Fix Script (Recommended)

1. **Upload the fix script to your production server:**
   - Copy `fix-database-migrations.php` to your backend directory on the server
   - Location: `/home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/`

2. **Run the fix script via SSH/Terminal:**
   ```bash
   cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend
   php fix-database-migrations.php
   ```

3. **Alternative: Run via cPanel:**
   - Access `https://backend.bgaofis.billurguleraslim.av.tr/backend/fix-database-migrations.php` in your browser
   - Review the output for any errors

### Option 2: Manual Migration

1. **Connect to your server via SSH/Terminal:**
   ```bash
   cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend
   ```

2. **Run the standard migration script:**
   ```bash
   php database/migrate.php
   ```

3. **Verify the migration worked:**
   ```bash
   php deploy.php
   ```

## What the Fix Script Does

The `fix-database-migrations.php` script performs these steps:

1. **Tests database connection** - Verifies your .env configuration
2. **Lists current tables** - Shows what's currently in your database
3. **Identifies missing tables** - Specifically checks for `finance_transactions` and others
4. **Runs migrations** - Creates all missing database tables
5. **Verifies creation** - Confirms all tables were created successfully
6. **Tests problematic queries** - Validates the dashboard queries work
7. **Provides next steps** - Guides you through testing

## Expected Tables After Fix

Your database should contain these tables:
- ✅ `users` - User accounts
- ✅ `roles` - User roles
- ✅ `permissions` - System permissions
- ✅ `cases` - Legal cases
- ✅ `case_parties` - Case participants
- ✅ `hearings` - Court hearings
- ✅ `documents` - Case documents
- ✅ `document_versions` - Document version history
- ✅ `finance_transactions` - **Financial transactions (was missing)**
- ✅ `workflow_templates` - Workflow templates
- ✅ `workflow_steps` - Workflow steps
- ✅ `notifications` - System notifications
- ✅ `pending_notifications` - Pending notifications
- ✅ `audit_logs` - Audit trail
- ✅ `tasks` - Task management

## Verification Steps

After running the fix script:

1. **Test the API endpoint:**
   ```bash
   curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard" \
     -H "Accept: application/json"
   ```

2. **Test the frontend application:**
   - Navigate to: `https://bgaofis.billurguleraslim.av.tr`
   - Check browser console for errors (should be clean now)
   - Try accessing the dashboard

3. **Run deployment verification:**
   ```bash
   php deploy.php
   ```

## Troubleshooting

### If the script fails with database connection errors:
1. Check your `.env` file configuration
2. Verify database credentials include cPanel username prefix
3. Ensure database user has all privileges on the database

### If migrations fail:
1. Check if tables already exist with different names
2. Look for any SQL syntax errors in the output
3. Verify you have sufficient database permissions

### If API still returns 500 errors:
1. Check server error logs in cPanel
2. Verify file permissions are correct
3. Ensure `.htaccess` is properly configured

## Prevention Measures

To prevent this issue in the future:

1. **Add migration check to deployment checklist**
2. **Create a pre-deployment verification script**
3. **Document the migration process**
4. **Set up automated deployment testing**

## Support

If you encounter any issues:

1. **Save the output** from the fix script
2. **Check browser console** for JavaScript errors
3. **Review server logs** in cPanel > Metrics > Errors
4. **Contact support** with the error details

---

## Quick Commands Summary

```bash
# Navigate to backend directory
cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend

# Run the automated fix
php fix-database-migrations.php

# Or run manual migration
php database/migrate.php

# Test deployment
php deploy.php

# Test API endpoint
curl -X GET "https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard" \
  -H "Accept: application/json"
```

This fix should resolve the 500 Internal Server Error and allow your dashboard to function properly.