<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Apresentacao;
use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Enums\StatusApresentacao;
use TrocaDeTurno\Repositories\ApresentacaoRepository;

final class ApresentacaoService
{
    public function __construct(
        private readonly ApresentacaoRepository $apresentacaoRepository,
        private readonly HorarioReferenciaService $horarioReferenciaService,
        private readonly EmpregadoService $empregadoService
    ) {}

    function obterEmpregado(string $matricula): Empregado
    {
        $empregado = $this->empregadoService->buscarEmpregadoPorMatricula($matricula);

        if (!$empregado) {
            throw new \InvalidArgumentException("Empregado com matrícula $matricula não encontrado.");
        }

        return $empregado;
    }
    
    public function obterApresentacaoPorId(int $idApresentacao): ?Apresentacao
    {
        return $this->apresentacaoRepository->buscarPorId($idApresentacao);
    }

    public function obterApresentacoes(): array
    {
        return $this->apresentacaoRepository->listarTodos();
    }

    public function jaApresentouHoje(int $idEmpregado): bool {
        return $this->apresentacaoRepository
            ->buscarApresentacaoHojePorIdEmpregado(
                $idEmpregado
            ) !== null;
    }

    public function registrarApresentacao(string $matricula, int $idLocal): void {

        $empregado = $this->obterEmpregado($matricula);

        if ($this->jaApresentouHoje($empregado->idEmpregado)) {
            throw new \RuntimeException(
                "Empregado já realizou apresentação hoje."
            );
        }
        $status = $this->horarioReferenciaService->estaAtrasadoChegada(idLocal: $idLocal, idTurno: $empregado->idTurno)
            ? StatusApresentacao::APRESENTADO_ATRASADO
            : StatusApresentacao::APRESENTADO;
        

        $this->apresentacaoRepository->registrar(
            idEmpregado: $empregado->idEmpregado,
            idLocal: $idLocal,
            status: $status->value
        );
    }

    public function atualizarStatusApresentacao(int $idApresentacao, ?int $idJustificativa): void {
        $apresentacao = $this->obterApresentacaoPorId($idApresentacao);

        if (!$apresentacao) {
            throw new \InvalidArgumentException(
                "Apresentação com ID $idApresentacao não encontrada."
            );
        }

        $novoStatus = $idJustificativa 
            ? StatusApresentacao::APRESENTADO_COM_JUSTIFICATIVA 
            : StatusApresentacao::APRESENTADO_ATRASADO;

        $this->apresentacaoRepository->atualizarStatus(
            idApresentacao: $idApresentacao,
            novoStatus: $novoStatus->value,
            idJustificativa: $idJustificativa
        );
    }
}
