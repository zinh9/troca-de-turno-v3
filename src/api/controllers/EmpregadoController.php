<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\EmpregadoService;

final class EmpregadoController
{
    public function __construct(private readonly EmpregadoService $service) {}

    public function buscar(): void
    {
        header('Content-Type: application/json');

        $matricula = $_GET['matricula'] ?? '';

        if (empty($matricula)) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Parâmetro matricula é obrigatório.'
            ]);

            return;
        }

        $empregado = $this->service->buscarEmpregadoPorMatricula($matricula);

        if ($empregado === null) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Empregado não encontrado.'
            ]);

            return;
        }

        echo json_encode(
            $empregado->paraArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }
}