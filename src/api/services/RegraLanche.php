<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Enums\IntervaloLanche;

/**
 * RegraLanche — "só METADE da equipe pode escolher CEDO (manhã); o resto
 * é obrigado a ir TARDE". Puro, sem banco.
 */
final class RegraLanche
{
    /**
     * Quantas pessoas podem escolher CEDO numa equipe de $totalEquipe.
     * TODO(Zenzo): arredondar pra cima (5 -> 3 cedo) ou pra baixo (5 -> 2 cedo)?
     * Pra cima garante que equipe de 1 pessoa consiga escolher CEDO. Troque
     * aqui se a regra for outra — é o ÚNICO lugar que decide isso.
     */
    public static function limiteCedo(int $totalEquipe): int
    {
        return (int) ceil($totalEquipe / 2);
    }

    public static function cedoDisponivel(int $totalEquipe, int $jaEscolheramCedo): bool
    {
        return $jaEscolheramCedo < self::limiteCedo($totalEquipe);
    }

    /**
     * Decide a escolha FINAL.
     *  - vaga em CEDO + pessoa pediu     -> o que ela pediu
     *  - sem vaga em CEDO                -> TARDE, forçado
     * Quem chama só chega aqui se a pessoa JÁ escolheu (ou se não há vaga).
     *
     * @return array{escolha: IntervaloLanche, forcado: bool}
     */
    public static function resolver(?IntervaloLanche $pedida, int $totalEquipe, int $jaEscolheramCedo): array
    {
        if (!self::cedoDisponivel($totalEquipe, $jaEscolheramCedo)) {
            return ['escolha' => IntervaloLanche::TARDE, 'forcado' => true];
        }

        return ['escolha' => $pedida ?? IntervaloLanche::TARDE, 'forcado' => false];
    }
}
