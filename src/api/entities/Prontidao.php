<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Prontidao
{
    public function __construct(
        public int $idProntidao,
        public ?\DateTimeImmutable $dataHoraProntidao, // null = CCP já acionou rádio mas ele ainda não marcou
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
            'dataHoraProntidao' => $this->dataHoraProntidao?->format('Y-m-d\TH:i:s'),
            'dataHoraChamadaCpt' => $this->dataHoraChamadaCpt?->format('Y-m-d\TH:i:s'),
            'status' => $this->status,
            'idApresentacao' => $this->idApresentacao,
            'idJustificativa' => $this->idJustificativa
        ];
    }
}