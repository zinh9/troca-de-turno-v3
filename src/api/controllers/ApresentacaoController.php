<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\ApresentacaoService;

/** Controller fino: lê parâmetros -> chama o service -> devolve array. Zero regra aqui. */
final class ApresentacaoController
{
    public function __construct(private readonly ApresentacaoService $apresentacaoService)
    {}

    /** POST /api/apresentacao  {matricula, idLocal, confirmarSupervisao?, confirmarTurno?} */
    public function registrar(): array
    {
        $d = Requisicao::dados();

        $resultado = $this->apresentacaoService->registrarApresentacao(
            matricula: Requisicao::texto($d, 'matricula'),
            idLocal: Requisicao::inteiro($d, 'idLocal'),
            confirmouSupervisao: Requisicao::booleano($d, 'confirmarSupervisao'),
            confirmouTurno: Requisicao::booleano($d, 'confirmarTurno'),
        );

        return ['success' => true] + $resultado;
    }

    /** POST /api/apresentacao/justificativa  {idApresentacao, idJustificativa} */
    public function justificar(): array
    {
        $d = Requisicao::dados();

        $this->apresentacaoService->justificarAtraso(
            Requisicao::inteiro($d, 'idApresentacao'),
            Requisicao::inteiro($d, 'idJustificativa'),
        );

        return ['success' => true, 'status' => 'OK'];
    }

    /** GET /api/apresentacoes (debug) */
    public function listar(): array
    {
        return array_map(fn($a) => $a->paraArray(), $this->apresentacaoService->obterApresentacoes());
    }
}
