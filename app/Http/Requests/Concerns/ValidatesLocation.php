<?php

namespace App\Http\Requests\Concerns;

trait ValidatesLocation
{
    /** @return array<string, mixed> */
    protected function locationRules(bool $required = false): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'address' => [$presence, 'string', 'max:240'],
            'city' => ['nullable', 'string', 'max:80'],
            'department' => ['nullable', 'string', 'max:80'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'place_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @param  array<string, mixed>  $payload */
    protected function locationPayload(array $payload): array
    {
        return [
            'address' => $payload['address'] ?? null,
            'city' => $payload['city'] ?? null,
            'department' => $payload['department'] ?? null,
            'lat' => $payload['lat'] ?? null,
            'lng' => $payload['lng'] ?? null,
            'place_id' => $payload['place_id'] ?? null,
        ];
    }
}
