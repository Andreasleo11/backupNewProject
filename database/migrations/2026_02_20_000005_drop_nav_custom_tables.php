<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the three custom nav assignment tables — replaced by Spatie nav.{route} permissions.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop in reverse dependency order
        Schema::dropIfExists('nav_menu_assignments');
        Schema::dropIfExists('nav_user_group_members');
        Schema::dropIfExists('nav_user_groups');
    }

    public function down(): void
    {
        // Intentionally empty — re-creating these tables is handled by
        // the original migration files which are preserved in git history.
    }
};
