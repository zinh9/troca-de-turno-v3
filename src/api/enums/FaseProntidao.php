<?php

declare(strict_types=1);

namespace TrocaDeTurno\Enums;

/**
 * Em que "momento" da prontidão o empregado está (conta a partir da apresentação):
 *   0 a 5 min   -> TOLERANCIA_INICIAL   (front: "Faça sua boa jornada, realize o TAC e o DSS")
 *   5 a 15 min  -> LIBERADO             (front: botão verde de prontidão)
 *   mais de 15  -> ATRASADO_JUSTIFICAR  (front: precisa escolher justificativa)
 */
enum FaseProntidao: string
{
    case TOLERANCIA_INICIAL = 'TOLERANCIA_INICIAL';
    case LIBERADO = 'LIBERADO';
    case ATRASADO_JUSTIFICAR = 'ATRASADO_JUSTIFICAR';
}
