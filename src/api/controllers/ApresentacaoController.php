<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Entities\Apresentacao;
use TrocaDeTurno\Services\ApresentacaoService;

final class ApresentacaoController
{
    public function __construct(private readonly ApresentacaoService $apresentacaoService)
    {}

    public function registrar(): void
    {
        $matricula = $_GET['matricula'] ?? '';
        $idLocal = (int) ($_GET['id-loc'] ?? 0);

        if (empty($matricula) && empty($idLocal)) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Parâmetro matricula e local é obrigatório.'
            ]);

            return;
        }

        $this->apresentacaoService->registrarApresentacao($matricula, $idLocal);
    }

    public function buscarPorId(): ?Apresentacao
    {
        header('Content-Type: application/json');

        $idApresentacao = (int) ($_GET['id-apresentacao'] ?? 0);

        if (empty($idApresentacao)) { 
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Parâmetro matricula e local é obrigatório.'
            ]);

            return null;
        }

        return $this->apresentacaoService->obterApresentacaoPorId($idApresentacao);
    }

    public function listar(): array
    {
        header('Content-Type: application/json');

        return $this->apresentacaoService->obterApresentacoes();
    }
}