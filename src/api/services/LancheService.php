<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Lanche;
use TrocaDeTurno\Repositories\LancheRepository;

final class LancheService
{
    public function __construct(
        private readonly LancheRepository $lancheRepository,
        private readonly EventoPublisherInterface $eventoPublisher
    ) {}

    public function obterPorIdApresentacao(int $idApresentacao): ?Lanche
    {
        return $this->lancheRepository->buscarPorIdApresentacao(idApresentacao: $idApresentacao);
    }

    public function registrarEscolha(int $idApresentacao, string $escolha, int $idSupervisao, ?int $idLocal): void
    {
        $this->lancheRepository->registrarEscolha(idApresentacao: $idApresentacao, escolha: $escolha);
        $this->eventoPublisher->publicar(idSupervisao: $idSupervisao, idLocal: $idLocal);
    }

    public function registrarInicio(int $idLanche, ?int $idJustificativaInicio, int $idSupervisao, ?int $idLocal): void
    {
        $this->lancheRepository->registrarInicioPatio(idLanche: $idLanche, idJustificativaInicio: $idJustificativaInicio);
        $this->eventoPublisher->publicar(idSupervisao: $idSupervisao, idLocal: $idLocal);
    }
}