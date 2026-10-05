<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Entities\Prontidao;
use TrocaDeTurno\Services\ProntidaoService;

final class ProntidaoController
{
    public function __construct(private readonly ProntidaoService $prontidaoService)
    {}

    /*
    public function enviarJustificativa(array $corpoRequisicao): array
    {
        $matricula = $corpoRequisicao['matricula'] ?? throw new \InvalidArgumentsException('matricula é obrigatoria');
        $idJustificativa = (int) ($corpoRequisicao['idJustificativa'] ?? throw new \InvalidArgumentsException('idJustificativa é obrigatoria'));

        $this->service->registrarComJustificativa($matricula, $idJustificativa);


        return ['success' => true];
    }
    */

    public function registrar(): void
    {
        $idApresentacao = (int) ($_GET['id-apresentacao'] ?? 0);
        $idJustificativa = $_GET['id-justificativa'] ?? null;

        if (empty($idApresentacao)) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Parâmetro id-apresentacao é obrigatório.'
            ]);

            return;
        }

        $this->prontidaoService->registrarProntidao($idApresentacao, $idJustificativa);
    }

    public function buscarPorId(): ?Prontidao
    {
        $idProntidao = (int) ($_GET['id-prontidao'] ?? 0);

        if (empty($idProntidao)) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Parâmetro id-apresentacao é obrigatório.'
            ]);

            return null;
        }

        return $this->prontidaoService->obterPorId($idProntidao);
    }

    public function listarTodos(): array
    {
        return $this->prontidaoService->obterProntidoes();
    }
}