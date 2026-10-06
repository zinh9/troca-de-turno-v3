<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Lanche
{
    public function __construct(
        public int $idLanche,
    
        public ?string $dataHoraLanchePatio,
        public ?string $dataHoraLancheCpt,
        public ?string $escolhaIntervaloLanche,
        public ?string $dataHoraProntidaoLanche,
    
        public int $idApresentacao,
    
        public ?int $idJustificativaProntidao,
        public ?int $idJustificativaInicio
    ){}
}