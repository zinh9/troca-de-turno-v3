<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

interface EventoPublisherInterface
{
    public function publicar(int $idSupervisao, ?int $idLocal): void;
    public function aguardarMudanca(int $idSupervisao, ?int $idLocal, int $timeoutSegundos): bool;
}