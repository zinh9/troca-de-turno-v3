<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Justificativa
{
    public function __construct(
        public int $idJustificativa,
        public string $descricao
    ) {
    }

    public function paraArray(): array
    {
        return ['idJustificativa' => $this->idJustificativa, 'descricao' => $this->descricao];
    }
}