<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Entities\HorarioReferencia;
use TrocaDeTurno\Repositories\HorarioReferenciaRepository;

final class HorarioReferenciaService
{
    public function __construct(
        private readonly HorarioReferenciaRepository $horarioReferenciaRepository
    ){}

    public function estaAtrasadoChegada(int $idTurno, int $idLocal): bool
    {
        $horarioChegada = $this->horarioReferenciaRepository->buscarPorLocalETurno($idTurno, $idLocal)->dataHoraReferenciaChegada;
        $agora = new \DateTimeImmutable();

        return $agora > $horarioChegada;
    }

    public function estaAtrasadoSaida(int $idTurno, int $idLocal): bool
    {
        $horarioSaida = $this->horarioReferenciaRepository->buscarPorLocalETurno($idTurno, $idLocal)->dataHoraReferenciaSaida;
        $agora = new \DateTimeImmutable();

        return $agora > $horarioSaida;
    }
}