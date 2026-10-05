<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Turno {
    public function __construct(
        public int $idTurno, 
        public string $turno
    ) {}

}
