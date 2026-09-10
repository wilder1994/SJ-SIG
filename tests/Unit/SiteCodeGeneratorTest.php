<?php

namespace Tests\Unit;

use App\Support\Structure\SiteCodeGenerator;
use PHPUnit\Framework\TestCase;

final class SiteCodeGeneratorTest extends TestCase
{
    public function test_uses_trailing_acronym_for_sos(): void
    {
        $generator = new SiteCodeGenerator;

        $this->assertSame(
            'SOS',
            $generator->prefix('ENTIDAD PROMOTORA DE SALUD SERVICIO OCCIDENTAL DE SALUD S A SOS'),
        );
    }

    public function test_uses_initials_when_there_is_no_short_acronym(): void
    {
        $generator = new SiteCodeGenerator;

        $this->assertSame('CM', $generator->prefix('Cliente mapa'));
        $this->assertSame('AM', $generator->prefix('Alcaldía Municipio A'));
    }
}
