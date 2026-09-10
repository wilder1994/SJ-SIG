<?php

namespace App\Support\Structure;

use App\Models\Contract;
use App\Models\Site;
use Illuminate\Support\Str;

final class SiteCodeGenerator
{
    /** @var list<string> */
    private const STOP = ['DE', 'DEL', 'LA', 'LAS', 'LOS', 'EL', 'Y', 'E', 'EN', 'S', 'A', 'SA', 'SAS', 'LTDA'];

    public function next(Contract $contract): string
    {
        $contract->loadMissing('tenant');
        $prefix = $this->prefix(
            (string) ($contract->tenant?->trade_name ?: $contract->tenant?->legal_name ?: $contract->tenant?->name ?: 'CLI'),
        );

        $used = Site::query()
            ->where('contract_id', $contract->id)
            ->pluck('code')
            ->map(fn (mixed $code) => strtoupper((string) $code))
            ->all();

        for ($n = 1; $n < 1000; $n++) {
            $code = $prefix.sprintf('%02d', $n);
            if (! in_array($code, $used, true)) {
                return $code;
            }
        }

        return $prefix.now()->format('His');
    }

    public function prefix(string $name): string
    {
        $normalized = $this->normalize($name);
        $tokens = array_values(array_filter(
            preg_split('/\s+/', $normalized) ?: [],
            fn (string $token) => $token !== '' && ! in_array($token, self::STOP, true),
        ));

        if ($tokens === []) {
            return 'CLI';
        }

        $last = $tokens[array_key_last($tokens)];
        if (strlen($last) >= 2 && strlen($last) <= 3) {
            return $last;
        }

        $initials = '';
        foreach ($tokens as $token) {
            $initials .= substr($token, 0, 1);
            if (strlen($initials) >= 4) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'CLI';
    }

    private function normalize(string $name): string
    {
        $upper = strtoupper(Str::ascii($name));

        return (string) preg_replace('/[^A-Z0-9\s]+/', ' ', $upper);
    }
}
