<?php

namespace Database\Seeders;

use App\Models\DefectCategory;
use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use App\Domain\Verification\Enums\Severity;
use App\Domain\Verification\Enums\DefectSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LegacyDefectCategoriesTransformerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!class_exists(DefectCategory::class)) {
            return;
        }

        $categories = DefectCategory::all();

        foreach ($categories as $cat) {
            $name = trim($cat->name);
            if (empty($name)) {
                continue;
            }

            // Generate a slugified uppercase code
            $code = strtoupper(Str::slug($name));
            if (empty($code)) {
                $code = 'LEGACY-' . $cat->id;
            }

            // Insert into the new catalog table
            DefectCatalog::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'default_severity' => Severity::LOW,
                    'default_source' => DefectSource::DAIJO,
                    'default_quantity' => 1.0000,
                    'notes' => '',
                    'active' => true,
                ]
            );
        }
    }
}
