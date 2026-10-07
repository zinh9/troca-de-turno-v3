<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TrocaDeTurno\Enums\FaseProntidao;
use TrocaDeTurno\Enums\StatusProntidao;
use TrocaDeTurno\Services\RegraProntidao;

/**
 * Padrão de TODO teste: ARRANGE (monta) -> ACT (executa) -> ASSERT (confere).
 * O relógio é inventado: apresentou 06:00:00, "agora" muda em cada teste.
 */
final class RegraProntidaoTest extends TestCase
{
    private function apresentou(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-10-07 06:00:00');
    }

    public function testNosPrimeiros5MinutosEhToleranciaInicial(): void
    {
        $fase = RegraProntidao::fase($this->apresentou(), new DateTimeImmutable('2026-10-07 06:04:59'));

        $this->assertSame(FaseProntidao::TOLERANCIA_INICIAL, $fase);
        $this->assertFalse(RegraProntidao::podeRegistrar($fase));
    }

    public function testComExatamente5MinutosLibera(): void
    {
        $fase = RegraProntidao::fase($this->apresentou(), new DateTimeImmutable('2026-10-07 06:05:00'));

        $this->assertSame(FaseProntidao::LIBERADO, $fase);
        $this->assertTrue(RegraProntidao::podeRegistrar($fase));
    }

    public function testExatamente15MinutosAindaEstaNoPrazo(): void
    {
        $fase = RegraProntidao::fase($this->apresentou(), new DateTimeImmutable('2026-10-07 06:15:00'));

        $this->assertSame(FaseProntidao::LIBERADO, $fase);
        $this->assertSame(StatusProntidao::PRONTO, RegraProntidao::statusAoRegistrar($fase));
    }

    public function test15MinutosE1SegundoJaEhAtraso(): void
    {
        $fase = RegraProntidao::fase($this->apresentou(), new DateTimeImmutable('2026-10-07 06:15:01'));

        $this->assertSame(FaseProntidao::ATRASADO_JUSTIFICAR, $fase);
        $this->assertTrue(RegraProntidao::exigeJustificativa($fase));
        $this->assertSame(StatusProntidao::PRONTO_COM_ATRASO_JUSTIFICADO, RegraProntidao::statusAoRegistrar($fase));
    }

    public function testHorariosParaOFrontLigarTimers(): void
    {
        $this->assertSame('2026-10-07 06:05:00', RegraProntidao::liberaEm($this->apresentou())->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-07 06:15:00', RegraProntidao::atrasaEm($this->apresentou())->format('Y-m-d H:i:s'));
    }
}
