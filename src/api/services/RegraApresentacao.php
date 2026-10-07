<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

/**
 * RegraApresentacao — SÓ matemática/lógica, zero banco, zero HTTP.
 * Por isso dá pra testar com PHPUnit em milissegundos (veja test/RegraApresentacaoTest.php).
 *
 * Regra de ouro: o relógio entra como PARÂMETRO ($agora). Quem chama passa
 * `new DateTimeImmutable()`; o teste passa uma hora fixa inventada.
 */
final class RegraApresentacao
{
    /** Atrasado = chegou DEPOIS do horário de referência de chegada (local + turno). */
    public static function estaAtrasado(\DateTimeImmutable $agora, \DateTimeImmutable $chegadaReferencia): bool
    {
        // TODO(Zenzo): turno 18x06 / 12x00 cruzam a meia-noite. Se a referência
        // de chegada vier "hoje 18:00" mas o empregado se apresentar 00:30,
        // esta comparação dá errado. Confirmar com o pessoal como o horário
        // de referência é guardado nesses turnos.
        return $agora > $chegadaReferencia;
    }

    public static function supervisaoDiferente(int $idSupervisaoDoLocal, int $idSupervisaoCadastrada): bool
    {
        return $idSupervisaoDoLocal !== $idSupervisaoCadastrada;
    }

    /**
     * Qual turno "é" agora, pela hora do relógio (janelas que você já tinha
     * no EmpregadoService, só que agora retornando o NOME do turno).
     * TODO(Zenzo): confirmar as janelas. Ex.: 06x18 vai de 05:45 até 11:40?
     */
    public static function turnoPelaHora(\DateTimeImmutable $agora): string
    {
        $hora = $agora->format('H:i');

        return match (true) {
            $hora >= '04:45' && $hora < '05:45' => '05x17',
            $hora >= '05:45' && $hora < '11:40' => '06x18',
            $hora >= '11:40' && $hora < '12:50' => '12x00',
            $hora >= '12:50' && $hora < '16:50' => '13x01',
            default => '18x06', // 16:50 até 04:45
        };
    }

    public static function turnoDiferente(string $turnoCadastrado, \DateTimeImmutable $agora): bool
    {
        return self::turnoPelaHora($agora) !== $turnoCadastrado;
    }
}
