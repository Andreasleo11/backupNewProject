# Sidebar Component Deprecation Plan

## Overview
This document outlines the deprecation of the old Livewire-based sidebar component in favor of the new Alpine.js + Tailwind sidebar system.

## Files to Deprecate

### 1. Core Livewire Component
- **File**: `app/Livewire/Sidebar.php`
- **Status**: ⚠️ DEPRECATED
- **Replacement**: Menu generation now handled by `App\Services\NavigationService`

### 2. Livewire View
- **File**: `resources/views/livewire/sidebar.blade.php`
- **Status**: ⚠️ DEPRECATED  
- **Replacement**: `resources/views/new/layouts/partials/sidebar-nav.blade.php`

### 3. Blade Components
- **Files**:
  - `resources/views/components/sidebar/group.blade.php`
  - `resources/views/components/sidebar/link.blade.php`
- **Status**: ⚠️ DEPRECATED
- **Replacement**: Integrated directly in `sidebar-nav.blade.php` using Alpine.js

### 4. Styles
- **Files**:
  - resources/sass/_sidebar.scss`
  - `resources/sass/_flyout.scss` (if only used for old sidebar)
- **Status**: ⚠️ DEPRECATED
- **Replacement**: Tailwind utility classes in new layout

### 5. Old Layout
- **File**: `resources/views/layouts/app.blade.php`
- **Line 31**: `<livewire:sidebar />`
- **Status**: ⚠️ TO BE UPDATED
- **Replacement**: `resources/views/new/layouts/app.blade.php`

## Migration Steps

### Phase 1: Identify All Usage
- [x] Located Livewire component
- [x] Located Livewire view  
- [x] Located blade components
- [x] Located SCSS files
- [x] Located layout file using old sidebar
- [ ] Identify all views extending `layouts.app`

### Phase 2: Mark as Deprecated (Add Warnings)
- [ ] Add deprecation comments to `app/Livewire/Sidebar.php`
- [ ] Add deprecation notice to `resources/views/livewire/sidebar.blade.php`
- [ ] Add deprecation notice to component files
- [ ] Add deprecation notice to old layout file

### Phase 3: Stop Using Old Layout
- [ ] Find all views using `@extends('layouts.app')`
- [ ] Migrate them to `@extends('new.layouts.app')`
- [ ] Test each migrated view

### Phase 4: Remove SCSS Imports
- [ ] Comment out `@import 'sidebar';` in `resources/sass/app.scss`
- [ ] Comment out `@import 'flyout';` in `resources/sass/app.scss` (if applicable)
- [ ] Test that styles still work

### Phase 5: Safe Removal
- [ ] Move deprecated files to `.deprecated/` directory
- [ ] Keep for one release cycle as backup
- [ ] Completely remove after confirmation

## Key Differences

### Old System (Livewire-based)
- **Framework**: Livewire component with Alpine.js
- **Styling**: Bootstrap 5 + Custom SCSS
- **Menu Logic**: Hardcoded in component with route pattern arrays
- **State**: Managed by Livewire properties
- **Search**: JavaScript-based filtering

### New System (Alpine.js + Tailwind)
- **Framework**: Pure Alpine.js (no Livewire)
- **Styling**: Tailwind CSS utility classes
- **Menu Logic**: Service-driven (`NavigationService::getPersonalizedMenu()`)
- **State**: Alpine.js `x-data` with `$persist` for sidebar collapsed state
- **Search**: Alpine.js reactive filtering with highlighted results

## Benefits of New System

1. **Better Performance**: No Livewire overhead for sidebar rendering
2. **Cleaner Code**: Service-based menu generation vs hardcoded arrays
3. **Modern UI**: Glassmorphism, gradients, smooth animations
4. **Better UX**: 
   - Search with highlighting
   - Flyout tooltips on collapsed state
   - Persistent sidebar preferences
5. **Easier Maintenance**: Single source of truth for navigation

## Rollback Plan

If issues arise:
1. Restore files from `.deprecated/` directory
2. Uncomment SCSS imports in `app.scss`
3. Update views back to `@extends('layouts.app')`
4. Run `npm run build`

## Timeline

- **Week 1**: Add deprecation notices, audit usage
- **Week 2**: Migrate critical views  
- **Week 3**: Migrate remaining views
- **Week 4**: Remove SCSS, move files to deprecated
- **Week 8**: Complete removal (after testing period)

## Contact

For questions about this migration, contact the development team.

---

**Last Updated**: 2026-01-28
**Status**: Planning Phase
