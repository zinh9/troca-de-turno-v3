<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Supervisao
{
    public function __construct(
        public int $idSupervisao,
        public string $supervisao
    ) {
    }

    public function paraArray(): array
    {
        return [
            'idSupervisao' => $this->idSupervisao,
            'supervisao'   => $this->supervisao
        ];
    }
}