<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\PainelService;

final class PainelController
{
    public function __construct(private readonly PainelService $painelService)
    {}

    public function obter(): array
    {
        $idSupervisao = (int) $_GET['id-supervisao'];
        $idLocal = (int) $_GET['id-local'];

        if (empty($idSupervisao) || $idSupervisao === 0) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Parametro vazio ou 0'];
        }

        $painel = $this->painelService->montarPainel(idSupervisao: $idSupervisao, idLocal: $idLocal);

        return [
            'success' => true,
            'info' => [
                'emManutencao' => false,
                'ultimaAtualizacao' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s'),
                'serverTime' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s'),
                'supervisao' => $idSupervisao,
                'local' => $idLocal
            ],
            'empregados' => $painel['empregados'],
        ];
    }
}