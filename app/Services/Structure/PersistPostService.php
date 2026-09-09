<?php

namespace App\Services\Structure;

use App\Enums\ServiceModality;
use App\Models\Post;
use App\Models\Site;

final class PersistPostService
{
    /** @param array{code: string, name: string, shift_hours: int, guard_slots: int} $payload */
    public function execute(Site $site, array $payload): Post
    {
        return Post::query()->create([
            'tenant_id' => $site->tenant_id,
            'contract_id' => $site->contract_id,
            'site_id' => $site->id,
            'code' => strtoupper($payload['code']),
            'name' => $payload['name'],
            'city' => $site->city,
            'shift_hours' => ServiceModality::from((int) $payload['shift_hours']),
            'guard_slots' => (int) $payload['guard_slots'],
        ]);
    }
}
