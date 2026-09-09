<?php

namespace App\Providers;

use App\Models\Contract;
use App\Models\Person;
use App\Policies\ContractPolicy;
use App\Policies\PersonPolicy;
use App\Repositories\ContractRepository;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Contracts\PersonRepositoryInterface;
use App\Repositories\PersonRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContractRepositoryInterface::class, ContractRepository::class);
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Person::class, PersonPolicy::class);
        Gate::policy(Contract::class, ContractPolicy::class);
    }
}
