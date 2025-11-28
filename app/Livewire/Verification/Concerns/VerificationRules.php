<?php

// app/Livewire/Verification/Concerns/VerificationRules.php

namespace App\Livewire\Verification\Concerns;

trait VerificationRules
{
    /* ---------------- Rules ---------------- */

    protected function rulesHeader(): array
    {
        return [
            'form.rec_date' => ['required', 'date'],
            'form.verify_date' => ['required', 'date', 'after_or_equal:form.rec_date'],
            'form.customer' => ['required', 'string', 'max:191'],
            'form.invoice_number' => ['required', 'string', 'max:191'],
            'form.meta' => ['nullable', 'array'],
        ];
    }

    protected function rulesItems(): array
    {
        return [
            'items' => ['array', 'min:1'],
            'items.*.part_name' => ['required', 'string', 'max:255'],
            'items.*.rec_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.verify_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.can_use' => ['required', 'numeric', 'min:0'],
            'items.*.cant_use' => ['required', 'numeric', 'min:0'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.currency' => ['required', 'string', 'max:10'],

        ];
    }

    protected function rulesDefects(): array
    {
        return [
            'items.*.defects' => ['array'],
            'items.*.defects.*.code' => ['nullable', 'string', 'max:64'],
            'items.*.defects.*.name' => ['required', 'required_with:items.*.defects.*.quantity', 'string', 'max:191'],
            'items.*.defects.*.severity' => ['nullable', 'in:LOW,MEDIUM,HIGH'],
            'items.*.defects.*.source' => ['required', 'in:DAIJO,CUSTOMER,SUPPLIER'],
            'items.*.defects.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.defects.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function rulesAll(): array
    {
        return array_merge($this->rulesHeader(), $this->rulesItems(), $this->rulesDefects());
    }

    /* ---------------- Messages ---------------- */

    // Friendly messages (cover the common ones + wildcards)
    protected function messagesAll(): array
    {
        return [
            // Header
            'form.rec_date.required' => ':attribute is required.',
            'form.rec_date.date' => ':attribute must be a valid date.',
            'form.verify_date.required' => ':attribute is required.',
            'form.verify_date.date' => ':attribute must be a valid date.',
            'form.verify_date.after_or_equal' => ':attribute cannot be before the receive date.',
            'form.customer.required' => ':attribute is required.',
            'form.invoice_number.required' => ':attribute is required.',

            // Items
            'items.min' => 'Please add at least one item.',
            'items.*.part_name.required' => ':attribute is required.',
            'items.*.rec_quantity.required' => ':attribute is required.',
            'items.*.verify_quantity.required' => ':attribute is required.',
            'items.*.can_use.required' => ':attribute is required.',
            'items.*.cant_use.required' => ':attribute quantity is required.',
            'items.*.price.required' => ':attribute is required.',
            'items.*.currency.required' => ':attribute is required.',
            
            // Defects (wildcards)
            'items.*.defects.*.name.required' => ':attribute is required.',
            'items.*.defects.*.name.required_with' => ':attribute is required when defect quantity is provided.',
            'items.*.defects.*.source.required' => ':attribute is required',
            'items.*.defects.*.quantity.required' => ':attribute is required',
            'items.*.defects.*.quantity.min' => ':attribute must be at least 1',
            'items.*.defects.*.severity.in' => ':attribute must be one of: LOW, MEDIUM, or HIGH.',
            'items.*.defects.*.source.in' => ':attribute must be DAIJO, CUSTOMER, or SUPPLIER.',
        ];
    }

    /* ---------------- Attribute labels ---------------- */

    // These replace the technical keys with human-friendly labels in messages
    protected function attributesAll(): array
    {
        return [
            'form.rec_date' => 'Receive Date',
            'form.verify_date' => 'Verify Date',
            'form.customer' => 'Customer',
            'form.invoice_number' => 'Invoice Number',
            'form.meta' => 'Additional Info',

            // Wildcards: Laravel will use these as base labels;
            'items' => 'items',
            'items.*.part_name' => 'Part Name',
            'items.*.rec_quantity' => 'Received Quantity',
            'items.*.verify_quantity' => 'Verified Quantity',
            'items.*.can_use' => 'Can quantity',
            'items.*.cant_use' => 'Can\'t quantity',
            'items.*.price' => 'Price',
            'items.*.currency' => 'Currency',
            'items.*.defects.*.code' => 'Defect Code',
            'items.*.defects.*.name' => 'Defect Name',
            'items.*.defects.*.severity' => 'Defect Severity',
            'items.*.defects.*.source' => 'Defect Source',
            'items.*.defects.*.quantity' => 'Defect Quantity',
            'items.*.defects.*.notes' => 'Defect Notes',
        ];
    }
}
