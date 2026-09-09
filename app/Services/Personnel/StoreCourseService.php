<?php

namespace App\Services\Personnel;

use App\Models\Course;
use App\Models\Person;

final class StoreCourseService
{
    public function execute(Person $person, string $title, string $takenOn): Course
    {
        return Course::query()->create([
            'tenant_id' => $person->tenant_id,
            'person_id' => $person->id,
            'title' => $title,
            'taken_on' => $takenOn,
        ]);
    }
}
