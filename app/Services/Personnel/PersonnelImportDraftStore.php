<?php

namespace App\Services\Personnel;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class PersonnelImportDraftStore
{
    private const TTL_MINUTES = 20;

    public function put(int $userId, int $contractId, UploadedFile $file): string
    {
        $token = (string) Str::uuid();
        $ext = strtolower((string) $file->getClientOriginalExtension()) === 'xls' ? 'xls' : 'xlsx';
        $path = $file->storeAs('imports/personnel/'.$userId, $token.'.'.$ext, 'local');

        Cache::put($this->key($userId, $token), [
            'contract_id' => $contractId,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
        ], now()->addMinutes(self::TTL_MINUTES));

        return $token;
    }

    /** @return array{contract_id: int, path: string, filename: string, absolute: string}|null */
    public function get(int $userId, string $token): ?array
    {
        $draft = Cache::get($this->key($userId, $token));
        if (! is_array($draft) || ! isset($draft['path'], $draft['contract_id'], $draft['filename'])) {
            return null;
        }

        $absolute = Storage::disk('local')->path((string) $draft['path']);
        if (! is_file($absolute)) {
            return null;
        }

        return [
            'contract_id' => (int) $draft['contract_id'],
            'path' => (string) $draft['path'],
            'filename' => (string) $draft['filename'],
            'absolute' => $absolute,
        ];
    }

    public function forget(int $userId, string $token): void
    {
        $draft = Cache::pull($this->key($userId, $token));
        if (is_array($draft) && isset($draft['path'])) {
            Storage::disk('local')->delete((string) $draft['path']);
        }
    }

    private function key(int $userId, string $token): string
    {
        return 'personnel-import:'.$userId.':'.$token;
    }
}
