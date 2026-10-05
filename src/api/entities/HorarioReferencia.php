<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class HorarioReferencia
{
    public function __construct(
        public int $idHorarioReferencia,
        public int $idLocal,
        public int $idTurno,
        public \DateTimeImmutable $dataHoraReferenciaChegada,
        public \DateTimeImmutable $dataHoraReferenciaSaida
    )
    {}
}