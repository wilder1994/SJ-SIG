<?php

namespace App\Http\Requests\Concerns;

trait AuthorizesAdmin
{
    public function authorize(): bool
    {
        return $this->user()?->role->canManageUsers() ?? false;
    }
}
