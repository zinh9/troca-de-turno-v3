<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TrocaDeTurno\Services\RegraApresentacao;

final class RegraApresentacaoTest extends TestCase
{
    public function testAntesDaReferenciaNaoEstaAtrasado(): void
    {
        $referencia = new DateTimeImmutable('2026-10-07 06:00:00');

        $this->assertFalse(RegraApresentacao::estaAtrasado(new DateTimeImmutable('2026-10-07 05:59:59'), $referencia));
        $this->assertFalse(RegraApresentacao::estaAtrasado(new DateTimeImmutable('2026-10-07 06:00:00'), $referencia));
    }

    public function testDepoisDaReferenciaEstaAtrasado(): void
    {
        $referencia = new DateTimeImmutable('2026-10-07 06:00:00');

        $this->assertTrue(RegraApresentacao::estaAtrasado(new DateTimeImmutable('2026-10-07 06:00:01'), $referencia));
    }

    public function testSupervisaoDiferente(): void
    {
        $this->assertTrue(RegraApresentacao::supervisaoDiferente(2, 1));
        $this->assertFalse(RegraApresentacao::supervisaoDiferente(1, 1));
    }

    public function testTurnoPelaHora(): void
    {
        $this->assertSame('06x18', RegraApresentacao::turnoPelaHora(new DateTimeImmutable('2026-10-07 06:02:00')));
        $this->assertSame('18x06', RegraApresentacao::turnoPelaHora(new DateTimeImmutable('2026-10-07 18:00:00')));
        $this->assertSame('18x06', RegraApresentacao::turnoPelaHora(new DateTimeImmutable('2026-10-07 02:00:00')));
    }

    public function testTurnoDiferenteDoCadastro(): void
    {
        $seisDaManha = new DateTimeImmutable('2026-10-07 06:02:00');

        $this->assertFalse(RegraApresentacao::turnoDiferente('06x18', $seisDaManha));
        $this->assertTrue(RegraApresentacao::turnoDiferente('18x06', $seisDaManha));
    }
}
