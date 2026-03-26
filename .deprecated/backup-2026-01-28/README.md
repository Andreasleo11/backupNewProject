# Archived Deprecated Sidebar Files

**Archive Date**: 2026-01-28  
**Reason**: Migration to new Alpine.js + Tailwind sidebar system

## What's In This Archive

This directory contains all the files related to the old Livewire-based sidebar component that was deprecated in favor of the new Alpine.js sidebar system.

### Files Archived

#### Application Files
- `app/Livewire/Sidebar.php` - The old Livewire component class
- `resources/views/livewire/sidebar.blade.php` - Old sidebar view with 594 lines
- `resources/views/components/sidebar/group.blade.php` - Sidebar group Blade component
- `resources/views/components/sidebar/link.blade.php` - Sidebar link Blade component
- `resources/views/layouts/app.blade.php` - Old layout file using the Livewire sidebar

#### SCSS Files
- `resources/sass/_sidebar.scss` - Old sidebar styles (155 lines)
- `resources/sass/_flyout.scss` - Flyout menu styles for collapsed sidebar (98 lines)

## Replacement System

All functionality from the old sidebar has been replaced by:

- **New Layout**: `resources/views/new/layouts/app.blade.php`
- **New Sidebar**: `resources/views/new/layouts/partials/sidebar-nav.blade.php`
- **Navigation Service**: `App\Services\NavigationService::getPersonalizedMenu()`
- **Styling**: Tailwind CSS utility classes (no custom SCSS needed)

## Migration Status

✅ **All views migrated** - No views in the application are using `@extends('layouts.app')`  
✅ **SCSS removed** - Sidebar SCSS imports commented out in `app.scss`  
✅ **Build successful** - Application rebuilt without issues  
✅ **Files archived** - All deprecated files safely backed up

## Rollback Instructions

If you need to rollback (should only be needed in emergency):

1. Restore files from this archive to their original locations
2. Uncomment the SCSS imports in `resources/sass/app.scss`:
   ```scss
   @import 'sidebar';
   @import 'flyout';
   ```
3. Run `npm run build`
4. Update any views to use `@extends('layouts.app')`

## Deletion Timeline

These files will be kept in archive for **one release cycle** (approximately 4-8 weeks) to ensure no issues arise from the migration. After that period, this entire archive can be safely deleted.

**Earliest safe deletion date**: 2026-03-28

## Questions?

See the main deprecation plan: `../.deprecation/SIDEBAR_DEPRECATION_PLAN.md`

---

*Archive created by automated migration process*
