<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Lanche
{
    public function __construct(
        public int $idLanche,
    
        public ?\DateTimeImmutable $dataHoraLanchePatio,
        public ?\DateTimeImmutable $dataHoraLancheCpt,
        public ?string $escolhaIntervaloLanche,
        public ?\DateTimeImmutable $dataHoraProntidaoLanche,
    
        public int $idApresentacao,
    
        public ?int $idJustificativaProntidao,
        public ?int $idJustificativaInicio
    ){}
}