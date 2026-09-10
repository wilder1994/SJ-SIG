<?php

namespace Tests;

use Database\Seeders\TestingSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function seed($class = null): static
    {
        return parent::seed($class ?? TestingSeeder::class);
    }
}
