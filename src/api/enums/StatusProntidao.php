<?php

declare(strict_types=1);

namespace TrocaDeTurno\Enums;

enum StatusProntidao: string
{
    case PRONTO = 'PRONTO';
    case PRONTO_COM_ATRASO_JUSTIFICADO = 'PRONTO_COM_ATRASO_JUSTIFICADO';
}