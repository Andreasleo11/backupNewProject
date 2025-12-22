<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table  = 'purchase_requests';
        $column = 'to_department';

        // MySQL / PostgreSQL compatible SQL (uses LOWER + TRIM)
        DB::statement("
            UPDATE {$table}
            SET {$column} =
                CASE
                    WHEN LOWER(TRIM({$column})) = 'maintenance' THEN 'MAINTENANCE'
                    WHEN LOWER(TRIM({$column})) = 'computer' THEN 'COMPUTER'
                    WHEN LOWER(TRIM({$column})) IN ('personnel','personalia') THEN 'PERSONALIA'
                    WHEN LOWER(TRIM({$column})) = 'purchasing' THEN 'PURCHASING'
                    ELSE UPPER(TRIM({$column}))
                END
            WHERE {$column} IS NOT NULL
              AND TRIM({$column}) <> ''
        ");
    }

    public function down(): void
    {
        // Down migration is intentionally non-reversible because:
        // - "PERSONALIA" could have come from "Personnel" or "Personalia"
        // - Case changes would lose original formatting
        //
        // If you really need reversibility, tell me what exact original labels you want restored.
    }
};
