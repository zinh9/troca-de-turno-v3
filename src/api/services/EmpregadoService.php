<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Repositories\EmpregadoRepository;

final class EmpregadoService
{
    public function __construct(
        private readonly EmpregadoRepository $empregadoRepository,
    )
    {}

    public function obterEmpregadoPorMatricula(string $matricula): ?Empregado
    {
        return $this->empregadoRepository->buscarPorMatricula($matricula);
    }

    public function listarTodosEmpregados(): array
    {
        return $this->empregadoRepository->listarTodos();
    }

    /** Troca o turno do cadastro (chamado quando ele CONFIRMA se apresentar em outro turno). */
    public function atualizarTurnoEmpregado(int $idEmpregado, int $idNovoTurno): void
    {
        $this->empregadoRepository->atualizarTurno($idEmpregado, $idNovoTurno);
    }
}
