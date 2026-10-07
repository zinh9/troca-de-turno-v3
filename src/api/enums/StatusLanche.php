<?php

declare(strict_types=1);

namespace TrocaDeTurno\Enums;

enum StatusLanche: string
{
    case CONCLUIDO = 'CONCLUIDO';
    case OITO_AS_DEZ_MEIA = '08:00 às 10:30';
    case QUARTOZE_AS_DEZESSEIS_MEIA = '14:00 às 16:30';
    case ATRASADO = 'ATRASADO';
    case EM_ANDAMENTO = 'EM_ANDAMENTO';
    case AGUARDANDO_JANELA = 'AGUARDANDO_JANELA';
    case AGUARDANDO_ESCOLHA = 'AGUARDANDO_ESCOLHA';
}