<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canRegisterMaintenance() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'electronic_asset_id' => ['required', 'integer'],
            'performed_on' => ['required', 'date'],
            'next_due_on' => ['nullable', 'date', 'after_or_equal:performed_on'],
            'summary' => ['required', 'string', 'max:255'],
            'evidence_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
