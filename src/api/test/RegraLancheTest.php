<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TrocaDeTurno\Enums\IntervaloLanche;
use TrocaDeTurno\Services\RegraLanche;

final class RegraLancheTest extends TestCase
{
    public function testEquipeDe4TemLimiteDe2NoCedo(): void
    {
        $this->assertSame(2, RegraLanche::limiteCedo(4));
    }

    public function testEquipeDe1PessoaPodeEscolherCedo(): void
    {
        $this->assertTrue(RegraLanche::cedoDisponivel(1, 0));
    }

    public function testComVagaRespeitaOPedido(): void
    {
        $r = RegraLanche::resolver(IntervaloLanche::CEDO, totalEquipe: 4, jaEscolheramCedo: 1);

        $this->assertSame(IntervaloLanche::CEDO, $r['escolha']);
        $this->assertFalse($r['forcado']);
    }

    public function testSemVagaForcaTarde(): void
    {
        $r = RegraLanche::resolver(IntervaloLanche::CEDO, totalEquipe: 4, jaEscolheramCedo: 2);

        $this->assertSame(IntervaloLanche::TARDE, $r['escolha']);
        $this->assertTrue($r['forcado']);
    }

    public function testQuemPedeTardeComVagaFicaComTarde(): void
    {
        $r = RegraLanche::resolver(IntervaloLanche::TARDE, totalEquipe: 4, jaEscolheramCedo: 0);

        $this->assertSame(IntervaloLanche::TARDE, $r['escolha']);
        $this->assertFalse($r['forcado']);
    }
}
