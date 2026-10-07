<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\ProntidaoService;

final class ProntidaoController
{
    public function __construct(private readonly ProntidaoService $prontidaoService)
    {}

    /** POST /api/prontidao  {idApresentacao, idJustificativa?, escolhaLanche?} */
    public function registrar(): array
    {
        $d = Requisicao::dados();

        $resultado = $this->prontidaoService->registrarProntidao(
            idApresentacao: Requisicao::inteiro($d, 'idApresentacao'),
            idJustificativa: Requisicao::inteiroOpcional($d, 'idJustificativa'),
            escolhaLanche: isset($d['escolhaLanche']) && $d['escolhaLanche'] !== '' ? (string) $d['escolhaLanche'] : null,
        );

        return ['success' => true] + $resultado;
    }

    /** POST /api/ccp/chamada  {idApresentacao}  -> botão "ACIONAR VIA RÁDIO" */
    public function acionarRadio(): array
    {
        $this->prontidaoService->acionarChamadaRadio(Requisicao::inteiro(Requisicao::dados(), 'idApresentacao'));

        return ['success' => true, 'status' => 'OK'];
    }

    /** GET /api/prontidoes (debug) */
    public function listarTodos(): array
    {
        return array_map(fn($p) => $p->paraArray(), $this->prontidaoService->obterProntidoes());
    }
}
