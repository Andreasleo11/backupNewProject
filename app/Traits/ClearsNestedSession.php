<?php

namespace App\Traits;

trait ClearsNestedSession
{
    /**
     * Forget a dot-notation child key and, if that leaves
     * the parent empty, forget the parent as well.
     */
    protected function forgetNestedKey(string $parent, string $child): void
    {
        session()->forget("$parent.$child");

        if (empty(session($parent))) {
            session()->forget($parent);
        }
    }
}
