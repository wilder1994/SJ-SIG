<?php

namespace App\Services\Structure;

use App\Enums\GuardRole;
use App\Enums\ServiceModality;
use App\Models\Post;
use App\Models\PostStaffing;
use App\Models\Site;

final class PersistPostService
{
    /**
     * @param  array{name: string, shift_hours: int, staffings: list<array{role: string, slots: int}>, code?: string}  $payload
     */
    public function execute(Site $site, array $payload): Post
    {
        $staffings = $this->normalizeStaffings($payload['staffings'] ?? []);
        $post = Post::query()->create([
            'tenant_id' => $site->tenant_id,
            'contract_id' => $site->contract_id,
            'site_id' => $site->id,
            'code' => $payload['code'] ?? $this->nextCode($site),
            'name' => $payload['name'],
            'city' => $site->city,
            'shift_hours' => ServiceModality::from((int) $payload['shift_hours']),
            'guard_slots' => $this->totalSlots($staffings),
        ]);

        $this->syncStaffings($post, $staffings);

        return $post->load('staffings');
    }

    /**
     * @param  array{name: string, shift_hours: int, staffings: list<array{role: string, slots: int}>}  $payload
     */
    public function update(Post $post, Site $site, array $payload): Post
    {
        $staffings = $this->normalizeStaffings($payload['staffings'] ?? []);
        $post->fill([
            'name' => $payload['name'],
            'city' => $site->city,
            'shift_hours' => ServiceModality::from((int) $payload['shift_hours']),
            'guard_slots' => $this->totalSlots($staffings),
        ])->save();

        $this->syncStaffings($post, $staffings);

        return $post->load('staffings');
    }

    public function nextCode(Site $site): string
    {
        $used = Post::query()
            ->where('contract_id', $site->contract_id)
            ->pluck('code')
            ->map(fn (mixed $code) => strtoupper((string) $code))
            ->all();

        for ($n = 1; $n < 1000; $n++) {
            $code = $site->code.'-'.sprintf('%02d', $n);
            if (! in_array($code, $used, true)) {
                return $code;
            }
        }

        return $site->code.'-'.now()->format('His');
    }

    /**
     * @param  list<array{role: string, slots: int}>  $staffings
     * @return list<array{role: GuardRole, slots: int}>
     */
    public function normalizeStaffings(array $staffings): array
    {
        $merged = [];
        foreach ($staffings as $row) {
            $role = GuardRole::from((string) $row['role']);
            $slots = (int) $row['slots'];
            if ($slots < 1) {
                continue;
            }
            $merged[$role->value] = ($merged[$role->value] ?? 0) + $slots;
        }

        $normalized = [];
        foreach ($merged as $role => $slots) {
            $normalized[] = ['role' => GuardRole::from($role), 'slots' => $slots];
        }

        return $normalized;
    }

    /** @param  list<array{role: GuardRole, slots: int}>  $staffings */
    public function totalSlots(array $staffings): int
    {
        $total = array_sum(array_map(fn (array $row) => $row['slots'], $staffings));

        return max(1, $total);
    }

    /** @param  list<array{role: GuardRole, slots: int}>  $staffings */
    public function label(array $staffings): string
    {
        if ($staffings === []) {
            return 'Sin unidades';
        }

        return collect($staffings)
            ->map(fn (array $row) => $row['role']->plural($row['slots']))
            ->implode(' · ');
    }

    /** @param  list<array{role: GuardRole, slots: int}>  $staffings */
    private function syncStaffings(Post $post, array $staffings): void
    {
        PostStaffing::query()->where('post_id', $post->id)->delete();

        foreach ($staffings as $row) {
            PostStaffing::query()->create([
                'tenant_id' => $post->tenant_id,
                'contract_id' => $post->contract_id,
                'post_id' => $post->id,
                'role' => $row['role'],
                'slots' => $row['slots'],
            ]);
        }
    }
}
