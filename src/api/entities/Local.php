<?php

declare(strict_types=1);

namespace TrocaDeTurno\Entities;

final readonly class Local
{
    public function __construct(
        public int $idLocal,
        public string $local,
        public int $idSupervisao
    ) {
    }

    public function paraArray(): array
    {
        return [
            'idLocal' => $this->idLocal,
            'local'   => $this->local
        ];
    }
}
