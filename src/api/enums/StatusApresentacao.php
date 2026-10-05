<?php

declare(strict_types=1);

namespace TrocaDeTurno\Enums;

enum StatusApresentacao: string
{
    case APRESENTADO = 'APRESENTADO';
    case APRESENTADO_COM_JUSTIFICATIVA = 'APRESENTADO_COM_JUSTIFICATIVA';

    case APRESENTADO_ATRASADO = 'APRESENTADO_ATRASADO';
}