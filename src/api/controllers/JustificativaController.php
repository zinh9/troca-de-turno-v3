<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\JustificativaService;

final class JustificativaController
{
    public function __construct(private readonly JustificativaService $service)
    {}

    public function listar(): array
    {
        return ['success' => true, 'justificativas' => $this->service->listarAtivas()];
    }
}