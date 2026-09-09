<?php

namespace App\Services\Dashboard;

use App\Domain\Dashboard\DashboardSnapshot;
use App\Enums\NoveltyStatus;
use App\Models\CompanyParafiscal;
use App\Models\Contract;
use App\Models\Maintenance;
use App\Models\Novelty;
use App\Models\Person;
use App\Models\PersonDocument;
use App\Models\Post;
use App\Models\ServiceDelivery;
use Carbon\CarbonImmutable;

final class BuildContractDashboardService
{
    public function execute(Contract $contract): DashboardSnapshot
    {
        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $horizon = CarbonImmutable::now()->addDays(30);

        $activePeople = Person::query()
            ->whereHas('contracts', fn ($q) => $q->where('contracts.id', $contract->id))
            ->whereNull('left_on')
            ->count();

        $withAffiliation = Person::query()
            ->whereHas('contracts', fn ($q) => $q->where('contracts.id', $contract->id))
            ->whereNull('left_on')
            ->whereNotNull('eps_name')
            ->whereNotNull('afp_name')
            ->whereNotNull('compensation_fund')
            ->count();

        $health = $activePeople === 0
            ? 0
            : (int) round(($withAffiliation / $activePeople) * 100);

        $services = ServiceDelivery::query()
            ->where('contract_id', $contract->id)
            ->where('period_kind', 'month')
            ->whereDate('period_starts_on', $monthStart)
            ->with('post.site')
            ->get();

        $posts = Post::query()->where('contract_id', $contract->id)->with('site')->orderBy('name')->get();
        $servedIds = $services->pluck('post_id')->all();

        $servicesByPost = $posts->map(fn (Post $post) => [
            'post' => trim(($post->site?->name ? $post->site->name.' · ' : '').$post->name),
            'quantity' => (int) ($services->firstWhere('post_id', $post->id)?->quantity ?? 0),
        ])->all();

        $expiring = PersonDocument::query()
            ->where('tenant_id', $contract->tenant_id)
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<=', $horizon)
            ->whereHas('person.contracts', fn ($q) => $q->where('contracts.id', $contract->id))
            ->with('person')
            ->orderBy('expires_on')
            ->limit(8)
            ->get()
            ->map(fn (PersonDocument $doc) => [
                'name' => $doc->person->full_name.' · '.$doc->original_name,
                'days' => (int) now()->startOfDay()->diffInDays($doc->expires_on, false),
            ])
            ->all();

        $maintenanceWatch = Maintenance::query()
            ->where('tenant_id', $contract->tenant_id)
            ->whereHas('asset', fn ($q) => $q->where('contract_id', $contract->id))
            ->with('asset')
            ->orderByRaw('CASE WHEN next_due_on IS NULL THEN 1 ELSE 0 END')
            ->orderBy('next_due_on')
            ->limit(6)
            ->get()
            ->map(fn (Maintenance $row) => [
                'asset' => $row->asset->name,
                'due' => $row->next_due_on?->format('d/m/Y') ?? 'Sin próxima fecha',
                'evidence' => $row->hasEvidence(),
            ])
            ->all();

        $openNovelties = Novelty::query()
            ->where('contract_id', $contract->id)
            ->where('status', NoveltyStatus::Open)
            ->with('post')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Novelty $row) => [
                'title' => $row->title,
                'post' => $row->post?->name,
                'opened' => $row->created_at?->format('d/m H:i') ?? '',
            ])
            ->all();

        return new DashboardSnapshot(
            contractName: $contract->name,
            tenantName: $contract->tenant->name,
            documentaryHealth: $health,
            activePeople: $activePeople,
            postsWithoutMonthService: $posts->whereNotIn('id', $servedIds)->count(),
            servicesByPost: $servicesByPost,
            expiringDocuments: $expiring,
            maintenanceWatch: $maintenanceWatch,
            openNovelties: $openNovelties,
            parafiscalMonthsOnFile: CompanyParafiscal::query()->where('contract_id', $contract->id)->count(),
        );
    }
}
