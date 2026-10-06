<?php 

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class FimJornada
{
    public function __construct(
        public int $idFimJornada,
        public ?\DateTimeImmutable $dataHoraFimJornadaPatio,
        public ?\DateTimeImmutable $dataHoraFimJornadaCpt,
        public int $idApresentacao,
        public ?int $idJustificativa
    ){}
}