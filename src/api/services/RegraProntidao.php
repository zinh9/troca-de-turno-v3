<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Enums\FaseProntidao;
use TrocaDeTurno\Enums\StatusProntidao;

/**
 * RegraProntidao — as regras de TEMPO da prontidão. Puro, sem banco.
 *
 *   apresentou ──5min──► botão verde ──até 15min──► prontidão normal
 *                                      └─ passou de 15min ─► exige justificativa
 */
final class RegraProntidao
{
    public const MINUTOS_MENSAGEM_INICIAL = 5;
    public const MINUTOS_LIMITE = 15;

    public static function minutosDecorridos(\DateTimeImmutable $apresentacao, \DateTimeImmutable $agora): float
    {
        // Usa SEGUNDOS (getTimestamp) em vez de ->diff()->i, que ignora segundos
        // e tinha o bug de considerar 15min59s como "no prazo".
        return ($agora->getTimestamp() - $apresentacao->getTimestamp()) / 60;
    }

    public static function fase(\DateTimeImmutable $apresentacao, \DateTimeImmutable $agora): FaseProntidao
    {
        $min = self::minutosDecorridos($apresentacao, $agora);

        return match (true) {
            $min < self::MINUTOS_MENSAGEM_INICIAL => FaseProntidao::TOLERANCIA_INICIAL,
            $min <= self::MINUTOS_LIMITE => FaseProntidao::LIBERADO,
            default => FaseProntidao::ATRASADO_JUSTIFICAR,
        };
    }

    public static function podeRegistrar(FaseProntidao $fase): bool
    {
        return $fase !== FaseProntidao::TOLERANCIA_INICIAL;
    }

    public static function exigeJustificativa(FaseProntidao $fase): bool
    {
        return $fase === FaseProntidao::ATRASADO_JUSTIFICAR;
    }

    public static function statusAoRegistrar(FaseProntidao $fase): StatusProntidao
    {
        return self::exigeJustificativa($fase)
            ? StatusProntidao::PRONTO_COM_ATRASO_JUSTIFICADO
            : StatusProntidao::PRONTO;
    }

    /** Hora em que o botão verde aparece (o front usa isso pra ligar um timer, sem polling). */
    public static function liberaEm(\DateTimeImmutable $apresentacao): \DateTimeImmutable
    {
        return $apresentacao->modify('+' . self::MINUTOS_MENSAGEM_INICIAL . ' minutes');
    }

    /** Hora em que passa a ser atraso (CCP: aparece "ACIONAR VIA RÁDIO"). */
    public static function atrasaEm(\DateTimeImmutable $apresentacao): \DateTimeImmutable
    {
        return $apresentacao->modify('+' . self::MINUTOS_LIMITE . ' minutes');
    }
}
