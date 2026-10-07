<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\PainelService;

final class PainelController
{
    public function __construct(private readonly PainelService $painelService)
    {}

    /**
     * GET /api/painel?idSupervisao=1&idLocal=7   (totem)
     * GET /api/painel?idSupervisao=1             (CCP: todos os locais)
     */
    public function obter(): array
    {
        $d = Requisicao::dados();
        $idSupervisao = Requisicao::inteiro($d, 'idSupervisao');
        $idLocal = Requisicao::inteiroOpcional($d, 'idLocal');

        $painel = $this->painelService->montarPainel($idSupervisao, $idLocal);
        $agora = (new \DateTimeImmutable())->format('Y-m-d\TH:i:s');

        return [
            'success' => true,
            'info' => [
                'emManutencao' => false,
                'ultimaAtualizacao' => $agora,
                'serverTime' => $agora,
                'supervisao' => $idSupervisao,
                'local' => $idLocal,
            ],
            'empregados' => $painel['empregados'],
        ];
    }
}
