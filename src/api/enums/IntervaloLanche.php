<?php

declare(strict_types=1);

namespace TrocaDeTurno\Enums;

/**
 * Qual janela de lanche o empregado vai usar.
 * CEDO  = "manhã"  (08:00 às 10:30)
 * TARDE = "tarde"  (14:00 às 16:30)
 * Os nomes CEDO/TARDE são os mesmos do contrato JSON do front.
 */
enum IntervaloLanche: string
{
    case CEDO = 'CEDO';
    case TARDE = 'TARDE';

    public function janela(): string
    {
        return match ($this) {
            self::CEDO => '08:00-10:30',
            self::TARDE => '14:00-16:30',
        };
    }
}
