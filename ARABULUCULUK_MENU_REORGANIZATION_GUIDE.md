# Arabuluculuk Menu Reorganization Guide

## Overview
This guide documents the complete reorganization of the menu structure to group all mediation (arabuluculuk) related items under a single "Arabuluculuk" heading.

## Changes Made

### 1. Database Structure Updates

#### New Migration File
- **File**: `backend/database/migrations/2024_01_05_000001_add_menu_hierarchy.php`
- **Purpose**: Adds `parent_id` column to support hierarchical menu structure

#### SQL Update Script
- **File**: `backend/arabuluculuk-menu-update.sql`
- **Purpose**: Direct SQL script to update database structure and menu data

### 2. Backend Changes

#### MenuItem Model Updates
- **File**: `backend/app/Models/MenuItem.php`
- **Changes**:
  - Added `parent_id` to fillable and casts
  - Added `parent()` and `children()` relationships for hierarchical structure

#### MenuController Updates
- **File**: `backend/app/Controllers/MenuController.php`
- **Changes**:
  - Updated all methods to handle `parent_id` field
  - Added `buildHierarchicalMenu()` method for creating nested menu structure
  - Added `sortMenuItemsRecursive()` method for proper ordering

#### MenuItemSeeder Updates
- **File**: `backend/database/seeders/MenuItemSeeder.php`
- **Changes**:
  - Restructured to create hierarchical menu with parent-child relationships
  - All mediation-related items are now under "Arabuluculuk" parent menu
  - Updated lawyer role permissions to include all mediation sub-items

### 3. Frontend Changes

#### MenuService Updates
- **File**: `frontend/src/services/MenuService.ts`
- **Changes**:
  - Added `parentId` and `children` fields to MenuItem interface
  - Now supports hierarchical menu structure

#### Sidebar Component Updates
- **File**: `frontend/src/components/Sidebar.tsx`
- **Changes**:
  - Added support for expandable/collapsible sub-menus
  - Added chevron icons for menu expansion
  - Recursive rendering of menu items with proper indentation

#### Route Updates
- **File**: `frontend/src/router/AppRoutes.tsx`
- **Changes**:
  - Added `/mediation/list` route for consistency
  - All mediation routes maintained and properly organized

#### Layout Updates
- **File**: `frontend/src/layouts/AppLayout.tsx`
- **Changes**:
  - Updated page titles to match new menu structure
  - Added titles for all mediation sub-pages

## New Menu Structure

### Main "Arabuluculuk" Menu (Parent)
- **Path**: `/mediation`
- **Label**: "Arabuluculuk"
- **Icon**: handshake

### Sub-menu Items (Children)
1. **Arabuluculuk Dosyaları**
   - Path: `/mediation/list`
   - Icon: list
   - Sort Order: 1

2. **Yeni Arabuluculuk Başvurusu**
   - Path: `/mediation/new`
   - Icon: add
   - Sort Order: 2

3. **Arabuluculuk Başvuruları**
   - Path: `/arbitration`
   - Icon: gavel
   - Sort Order: 3

4. **Arabuluculuk İstatistikleri**
   - Path: `/arbitration/dashboard`
   - Icon: bar_chart
   - Sort Order: 4

## Deployment Instructions

### Option 1: Using SQL Script (Recommended)
1. Run the SQL script directly on your database:
   ```bash
   mysql -u username -p database_name < backend/arabuluculuk-menu-update.sql
   ```

### Option 2: Using Migration Runner
1. Run the migration:
   ```bash
   cd backend
   php run-migrations.php
   ```

2. Run the seeder:
   ```bash
   php database/seed.php
   ```

### Option 3: Using Custom Update Script
1. Run the custom update script:
   ```bash
   cd backend
   php update-menu-structure.php
   ```

## Verification Steps

### 1. Backend Verification
- Check that the `menu_items` table has the `parent_id` column
- Verify that menu items are properly structured with parent-child relationships
- Test the `/api/menu/my` endpoint returns hierarchical structure

### 2. Frontend Verification
- Load the application and check the sidebar menu
- Verify that "Arabuluculuk" menu appears with expandable sub-items
- Test that all sub-menu items are accessible and functional
- Verify proper permissions for different user roles

### 3. Functional Testing
- Test each mediation-related page:
  - `/mediation/list` - Arabuluculuk Dosyaları
  - `/mediation/new` - Yeni Arabuluculuk Başvurusu
  - `/arbitration` - Arabuluculuk Başvuruları
  - `/arbitration/dashboard` - Arabuluculuk İstatistikleri

## Benefits of the New Structure

1. **Better Organization**: All mediation-related functionality is grouped under one menu
2. **Improved Navigation**: Users can easily find all mediation features
3. **Scalability**: Easy to add new mediation-related sub-items
4. **Consistent UX**: Hierarchical menu structure matches modern UI patterns
5. **Role-based Access**: Proper permission management for different user types

## Rollback Plan

If needed, you can rollback by:
1. Restoring the original `MenuItemSeeder.php`
2. Removing the `parent_id` column from `menu_items` table
3. Running the original seeder to restore flat menu structure

## Notes

- The migration includes foreign key constraints to ensure data integrity
- All existing functionality is preserved, just reorganized
- The frontend sidebar now supports expandable menus with smooth animations
- Menu permissions are properly maintained for both admin and lawyer roles