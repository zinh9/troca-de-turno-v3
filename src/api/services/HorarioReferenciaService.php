<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Repositories\HorarioReferenciaRepository;

final class HorarioReferenciaService
{
    public function __construct(
        private readonly HorarioReferenciaRepository $horarioReferenciaRepository
    ){}

    public function estaAtrasadoChegada(int $idLocal, int $idTurno): bool
    {
        $horarioChegada = $this->horarioReferenciaRepository->buscarPorLocalETurno(idLocal: $idLocal, idTurno: $idTurno)->dataHoraReferenciaChegada;
        $agora = new \DateTimeImmutable();

        return $agora > $horarioChegada;
    }

    public function estaAtrasadoSaida(int $idLocal, int $idTurno): bool
    {
        $horarioSaida = $this->horarioReferenciaRepository->buscarPorLocalETurno(idLocal: $idLocal, idTurno: $idTurno)->dataHoraReferenciaSaida;
        $agora = new \DateTimeImmutable();

        return $agora > $horarioSaida;
    }
}