<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

interface EventoPublisherInterface
{
    public function publicar(string $supervisao, ?string $local): void;
    public function aguardarMudanca(string $supervisao, ?string $local, int $timeoutSegundos): bool;
}