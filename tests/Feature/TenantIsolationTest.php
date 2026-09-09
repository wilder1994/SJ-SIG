<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_cannot_see_the_other_contract_universe(): void
    {
        $this->seed();

        $supervisorA = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();
        $supervisorB = User::query()->where('email', 'supervisor.b@sj-sig.test')->firstOrFail();
        $personB = Person::query()->where('document_number', '52220001')->firstOrFail();

        $this->actingAs($supervisorA)
            ->get('/personal')
            ->assertOk()
            ->assertSee('Ana Vigilante A')
            ->assertDontSee('Bruno Vigilante B');

        $this->actingAs($supervisorA)
            ->get('/personal/'.$personB->id)
            ->assertNotFound();

        $this->actingAs($supervisorA)
            ->get('/documentos')
            ->assertOk()
            ->assertSee('Ana Vigilante A')
            ->assertDontSee('Bruno Vigilante B');

        $this->actingAs($supervisorA)
            ->get('/documentos/carpeta/'.$personB->id)
            ->assertNotFound();

        $this->actingAs($supervisorB)
            ->get('/personal')
            ->assertOk()
            ->assertSee('Bruno Vigilante B')
            ->assertDontSee('Ana Vigilante A');
    }
}
