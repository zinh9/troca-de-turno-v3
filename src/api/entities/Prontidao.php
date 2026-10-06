<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Prontidao
{
    public function __construct(
        public int $idProntidao,
        public \DateTimeImmutable $dataHoraProntidao,
        public ?\DateTimeImmutable $dataHoraChamadaCpt,
        public string $status,
        public int $idApresentacao,
        public ?int $idJustificativa = null
    ) {
    }

    public function paraArray(): array
    {
        return [
            'idProntidao' => $this->idProntidao,
            'dataHoraProntidao' => $this->dataHoraProntidao,
            'dataHoraChamadaCpt' => $this->dataHoraChamadaCpt,
            'status' => $this->status,
            'idApresentacao' => $this->idApresentacao,
            'idJustificativa' => $this->idJustificativa
        ];
    }
}