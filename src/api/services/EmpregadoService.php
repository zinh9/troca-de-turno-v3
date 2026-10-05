<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Repositories\EmpregadoRepository;

final class EmpregadoService
{
    public function __construct(
        private readonly EmpregadoRepository $empregadoRepository
    )
    {}

    public function buscarEmpregadoPorMatricula(string $matricula): ?Empregado
    {
        return $this->empregadoRepository->buscarPorMatricula($matricula);
    }

    public function listarTodosEmpregados(): array
    {
        return $this->empregadoRepository->listarTodos();
    }

    public function atualizarTurnoEmpregado(string $matricula, string $novoTurno): bool
    {
        $empregado = $this->empregadoRepository->buscarPorMatricula($matricula);

        if (!$empregado) {
            return false;
        }

        if ($empregado->turno === $novoTurno) {
            return true; // O turno já está atualizado, não é necessário fazer nada
        }

        if (!$this->empregadoRepository->atualizarTurno($matricula, $novoTurno)) {
            return false; // Falha ao atualizar o turno
        }

        return true;
    }
}