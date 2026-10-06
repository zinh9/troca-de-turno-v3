<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Apresentacao
{
    public function __construct(
        public int $idApresentacao,
        public \DateTimeImmutable $dataHoraApresentacao,
        public int $idEmpregado,
        public int $idLocal,
        public string $status,
        public ?int $idJustificativa = null
    ) {}

    public function paraArray(): array
    {
        return [
            'idApresentacao' => $this->idApresentacao,
            'dataHoraApresentacao' => $this->dataHoraApresentacao->format('Y-m-d H:i:s'),
            'idEmpregado' => $this->idEmpregado,
            'idLocal' => $this->idLocal,
            'status' => $this->status,
            'idJustificativa' => $this->idJustificativa
        ];
    }
}