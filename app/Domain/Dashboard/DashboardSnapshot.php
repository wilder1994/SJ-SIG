<?php

namespace App\Domain\Dashboard;

final readonly class DashboardSnapshot
{
    /**
     * @param  list<array{post: string, quantity: int}>  $servicesByPost
     * @param  list<array{name: string, days: int}>  $expiringDocuments
     * @param  list<array{asset: string, due: string, evidence: bool}>  $maintenanceWatch
     * @param  list<array{title: string, post: ?string, opened: string}>  $openNovelties
     */
    public function __construct(
        public string $contractName,
        public string $tenantName,
        public int $documentaryHealth,
        public int $activePeople,
        public int $postsWithoutMonthService,
        public array $servicesByPost,
        public array $expiringDocuments,
        public array $maintenanceWatch,
        public array $openNovelties,
        public int $parafiscalMonthsOnFile,
    ) {}
}
