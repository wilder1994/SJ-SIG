<?php

namespace App\Services\Personnel;

use App\Models\Person;
use App\Support\Files\StoredFileResponder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class StorePersonPhotoService
{
    public function execute(Person $person, UploadedFile $photo): Person
    {
        if (is_string($person->photo_path) && $person->photo_path !== '') {
            $previous = StoredFileResponder::absolute($person->photo_path);
            if (is_file($previous)) {
                File::delete($previous);
            }
        }

        $relative = sprintf(
            'tenants/%d/people/%d/avatar/%s.%s',
            $person->tenant_id,
            $person->id,
            Str::uuid()->toString(),
            strtolower($photo->getClientOriginalExtension() ?: 'jpg'),
        );
        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));
        $photo->move(dirname($absolute), basename($absolute));

        $person->photo_path = $relative;
        $person->save();

        return $person;
    }
}
