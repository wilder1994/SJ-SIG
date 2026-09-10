<?php

namespace App\Services\Structure;

use App\Enums\SiteServiceEventKind;
use App\Models\Contract;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteServiceEvent;
use App\Support\Structure\SiteCodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PersistSiteService
{
    public function __construct(
        private readonly SiteCodeGenerator $codes,
        private readonly PersistPostService $posts,
    ) {}

    /** @param array<string, mixed> $payload */
    public function execute(Contract $contract, array $payload): Site
    {
        return DB::transaction(function () use ($contract, $payload) {
            $site = Site::query()->create([
                'tenant_id' => $contract->tenant_id,
                'contract_id' => $contract->id,
                'code' => $this->codes->next($contract),
                'name' => $payload['name'],
                'city' => ($payload['city'] ?? null) ?: null,
                'address' => $payload['address'] ?? null,
                'department' => $payload['department'] ?? null,
                'lat' => $payload['lat'] ?? null,
                'lng' => $payload['lng'] ?? null,
                'place_id' => $payload['place_id'] ?? null,
            ]);

            $created = $this->syncPosts($site, $payload['posts'] ?? []);
            $site->load(['posts.staffings']);

            if ($created !== [] || filled($payload['service_start'] ?? null)) {
                $this->record($site, SiteServiceEventKind::Inicio, [
                    'effective_on' => $payload['effective_on'] ?? now()->toDateString(),
                    'reason' => $payload['service_start'] ?? null,
                    'requested_by' => $payload['requested_by'] ?? null,
                    'from_summary' => null,
                    'to_summary' => $site->unitsLabel(),
                    'snapshot' => ['to' => $this->snapshot($site)],
                ]);
            }

            return $site;
        });
    }

    /** @param array<string, mixed> $payload */
    public function update(Site $site, array $payload): Site
    {
        return DB::transaction(function () use ($site, $payload) {
            $site->load(['posts.staffings']);
            $before = $this->snapshot($site);
            $beforeLabel = $site->unitsLabel();
            $beforeIdentity = $this->identityLabel($site);
            $scopes = $this->changeScopes($site, $payload, $before);

            if ($scopes !== []) {
                $this->assertChangeNote($payload, $scopes);
            }

            $site->fill([
                'name' => $payload['name'],
                'city' => ($payload['city'] ?? null) ?: null,
                'address' => $payload['address'] ?? null,
                'department' => $payload['department'] ?? null,
                'lat' => $payload['lat'] ?? null,
                'lng' => $payload['lng'] ?? null,
                'place_id' => $payload['place_id'] ?? null,
            ])->save();

            $this->syncPosts($site, $payload['posts'] ?? []);
            $site->load(['posts.staffings']);

            if ($scopes !== []) {
                $this->record($site, SiteServiceEventKind::Cambio, [
                    'effective_on' => now()->toDateString(),
                    'reason' => $payload['change_reason'],
                    'from_summary' => $this->changeFromSummary($scopes, $beforeLabel, $beforeIdentity),
                    'to_summary' => $this->changeToSummary($scopes, $site),
                    'snapshot' => [
                        'scopes' => $scopes,
                        'from' => $before,
                        'to' => $this->snapshot($site),
                    ],
                ]);
            }

            return $site;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $posts
     * @return list<Post>
     */
    private function syncPosts(Site $site, array $posts): array
    {
        $saved = [];

        foreach ($posts as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $staffings = $row['staffings'] ?? [];
            if ($name === '' || $staffings === []) {
                continue;
            }

            $payload = [
                'name' => $name,
                'shift_hours' => (int) $row['shift_hours'],
                'staffings' => $staffings,
            ];

            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $post = Post::query()
                    ->whereKey($id)
                    ->where('site_id', $site->id)
                    ->first();
                if ($post === null) {
                    continue;
                }
                $saved[] = $this->posts->update($post, $site, $payload);

                continue;
            }

            $saved[] = $this->posts->execute($site, $payload);
        }

        return $saved;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<array{id: int|string, name: string, hours: int, lines: list<string>}>  $before
     * @return list<string>
     */
    private function changeScopes(Site $site, array $payload, array $before): array
    {
        $scopes = [];

        if ($this->siteIdentityChanged($site, $payload)) {
            $scopes[] = 'instalacion';
        }

        $incoming = $this->incomingSnapshot($payload['posts'] ?? []);
        $oldById = collect($before)->keyBy(fn (array $row) => (string) $row['id']);

        foreach ($incoming as $row) {
            $id = (string) $row['id'];
            $old = $oldById->get($id);
            if ($old === null || str_starts_with($id, 'new:')) {
                $scopes[] = 'puesto';

                continue;
            }

            if ($this->norm((string) $old['name']) !== $this->norm((string) $row['name'])
                || (int) $old['hours'] !== (int) $row['hours']) {
                $scopes[] = 'puesto';
            }

            $oldLines = collect($old['lines'] ?? [])->sort()->values()->all();
            $newLines = collect($row['lines'] ?? [])->sort()->values()->all();
            if ($oldLines !== $newLines) {
                $scopes[] = 'personal';
            }
        }

        return array_values(array_unique($scopes));
    }

    /** @param array<string, mixed> $payload */
    private function siteIdentityChanged(Site $site, array $payload): bool
    {
        return $this->norm((string) $site->name) !== $this->norm((string) ($payload['name'] ?? ''))
            || $this->norm((string) $site->city) !== $this->norm((string) ($payload['city'] ?? ''))
            || $this->norm((string) $site->address) !== $this->norm((string) ($payload['address'] ?? ''))
            || $this->norm((string) $site->department) !== $this->norm((string) ($payload['department'] ?? ''))
            || $this->coord($site->lat) !== $this->coord($payload['lat'] ?? null)
            || $this->coord($site->lng) !== $this->coord($payload['lng'] ?? null);
    }

    /** @param list<string> $scopes */
    private function changeFromSummary(array $scopes, string $beforeLabel, string $beforeIdentity): string
    {
        $phrase = 'Cambio en '.$this->scopesPhrase($scopes);

        if (in_array('personal', $scopes, true) || in_array('puesto', $scopes, true)) {
            return $beforeLabel !== '' ? $phrase.': '.$beforeLabel : $phrase;
        }

        return $beforeIdentity !== '' ? $phrase.': '.$beforeIdentity : $phrase;
    }

    /** @param list<string> $scopes */
    private function changeToSummary(array $scopes, Site $site): string
    {
        if (in_array('personal', $scopes, true) || in_array('puesto', $scopes, true)) {
            return $site->unitsLabel();
        }

        return $this->identityLabel($site);
    }

    private function identityLabel(Site $site): string
    {
        return collect([$site->name, $site->city, $site->address])->filter()->implode(' · ');
    }

    private function scopesPhrase(array $scopes): string
    {
        $labels = [
            'instalacion' => 'instalación',
            'puesto' => 'puesto',
            'personal' => 'personal',
        ];
        $words = array_values(array_filter(array_map(fn (string $scope) => $labels[$scope] ?? null, $scopes)));

        if ($words === []) {
            return 'la ficha';
        }
        if (count($words) === 1) {
            return $words[0];
        }
        if (count($words) === 2) {
            return $words[0].' y '.$words[1];
        }

        return $words[0].', '.$words[1].' y '.$words[2];
    }

    private function norm(?string $value): string
    {
        return trim((string) $value);
    }

    private function coord(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 7, '.', '');
    }

    /**
     * @param  list<array<string, mixed>>  $posts
     * @return list<array{id: string, name: string, hours: int, lines: list<string>}>
     */
    private function incomingSnapshot(array $posts): array
    {
        $rows = [];
        foreach ($posts as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $staffings = $this->posts->normalizeStaffings($row['staffings'] ?? []);
            if ($name === '' || $staffings === []) {
                continue;
            }

            $rows[] = [
                'id' => (string) (($row['id'] ?? '') !== '' && (int) $row['id'] > 0 ? $row['id'] : 'new:'.$name),
                'name' => $name,
                'hours' => (int) $row['shift_hours'],
                'lines' => collect($staffings)
                    ->map(fn (array $line) => $line['role']->value.':'.$line['slots'])
                    ->all(),
            ];
        }

        return $rows;
    }

    /** @return list<array{id: int, name: string, hours: int, lines: list<string>}> */
    private function snapshot(Site $site): array
    {
        return $site->posts->map(fn (Post $post) => [
            'id' => $post->id,
            'name' => $post->name,
            'hours' => $post->shift_hours->value,
            'lines' => $post->staffings
                ->map(fn ($row) => $row->role->value.':'.$row->slots)
                ->values()
                ->all(),
        ])->values()->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $scopes
     */
    private function assertChangeNote(array $payload, array $scopes): void
    {
        if (! filled($payload['change_reason'] ?? null)) {
            throw ValidationException::withMessages([
                'change_reason' => 'Indique el motivo del cambio en '.$this->scopesPhrase($scopes).'.',
            ]);
        }
    }

    /** @param array<string, mixed> $payload */
    private function record(Site $site, SiteServiceEventKind $kind, array $payload): void
    {
        SiteServiceEvent::query()->create([
            'tenant_id' => $site->tenant_id,
            'contract_id' => $site->contract_id,
            'site_id' => $site->id,
            'kind' => $kind,
            'effective_on' => $payload['effective_on'],
            'last_shift_on' => $payload['last_shift_on'] ?? null,
            'requested_by' => $payload['requested_by'] ?? null,
            'reason' => $payload['reason'] ?? null,
            'from_summary' => $payload['from_summary'] ?? null,
            'to_summary' => $payload['to_summary'] ?? null,
            'snapshot' => $payload['snapshot'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
