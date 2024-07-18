<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SanitizedNumeric implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Add your sanitization logic here
        $sanitizedValue = preg_replace('/[^\d.]/', '', $value);
        if (!is_numeric($sanitizedValue)) {
            // $fail('The :attribute must be a valid numeric value after sanitization.');
            $fail('The item must be a valid price.');
        }
    }
}
