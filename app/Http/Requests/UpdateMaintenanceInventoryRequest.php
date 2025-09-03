<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "master_id" => "required|exists:master_inventories,id",
            "revision_date" => "nullable|date",
            "items" => "required|array",
            "items.*" => "exists:detail_maintenance_inventory_reports,id",
            "conditions" => "required|array",
            "conditions.*" => "in:good,bad",
            "remarks" => "required|array",
            "checked_by" => "required|array",
            "checked_by.*" => "exists:users,name",
            "new_items" => "array",
            "new_items_names" => "array|required_with:new_items",
            "new_items_names.*" => "required|string",
            "new_conditions" => "array|required_with:new_items",
            "new_conditions.*" => "in:good,bad",
            "new_remarks" => "array|required_with:new_items",
        ];
    }

    public function messages()
    {
        return [
            "master_id.required" => "The master inventory is required.",
            "master_id.exists" => "The selected master inventory does not exist.",
            "revision_date.date" => "The revision date is not a valid date.",
            "items.required" => "At least one item must be selected.",
            "items.*.exists" => "One or more selected items do not exist.",
            "conditions.required" => "Conditions are required.",
            "conditions.*.in" => "Each condition must be either good or bad.",
            "remarks.required" => "Remarks are required.",
            "checked_by.required" => "Checked by field is required.",
            "checked_by.*.exists" => "The selected checker does not exist.",
            "new_items_names.*.required" => "The name for each new item is required.",
            "new_conditions.*.in" => "Each new condition must be either good or bad.",
        ];
    }
}
