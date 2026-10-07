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

    public function verificarSupervisaoDiferente(int $idSupervisaoApresentacao, int $idSupervisaoOriginal): bool
    {
        return $idSupervisaoApresentacao <> $idSupervisaoOriginal;
    }

    public function verificarTurnoDiferente(string $turno): bool
    {
        $horaAgora = (new \DateTimeImmutable())->format('H:i');

        return match ($turno) {
            '05x17' => $horaAgora >= '04:45' && $horaAgora < '05:45',
            '06x18' => $horaAgora >= '05:45' && $horaAgora < '11:40',
            '12x00' => $horaAgora >= '11:40' && $horaAgora < '12:50',
            '13x01' => $horaAgora >= '12:50' && $horaAgora < '16:50',
            '18x06' => $horaAgora >= '16:50' || $horaAgora < '04:45',
            default => false,
        };
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