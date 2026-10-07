<?php

declare(strict_types=1);

namespace TrocaDeTurno\Enums;

enum StatusProntidao: string
{
    // Linha criada pelo CCP ao "ACIONAR VIA RÁDIO" antes do empregado marcar.
    case AGUARDANDO = 'AGUARDANDO';
    case PRONTO = 'PRONTO';
    case PRONTO_COM_ATRASO_JUSTIFICADO = 'PRONTO_COM_ATRASO_JUSTIFICADO';
}
