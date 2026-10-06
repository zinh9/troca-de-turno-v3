<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Refeicao
{
    public function __construct(
        public int $idRefeicao,
        public ?\DateTimeImmutable $dataHoraRefeicaoPatio,
        public ?\DateTimeImmutable $dataHoraRefeicaoCpt,
        public ?\DateTimeImmutable $dataHoraProntidaoRefeicao,
        public int $idApresentacao,
        public ?int $idJustificativaProntidao,
        public ?int $idJustificativaInicio
    ){}
}