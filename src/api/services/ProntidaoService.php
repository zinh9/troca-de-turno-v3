<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Apresentacao;
use TrocaDeTurno\Entities\Prontidao;
use TrocaDeTurno\Enums\StatusProntidao;
use trocadeturno\events\EventDispatcher;
use TrocaDeTurno\Repositories\ProntidaoRepository;

final class ProntidaoService
{
    public function __construct(
        private readonly ProntidaoRepository $prontidaoRepository,
        // private readonly EventDispatcher $dispatcher,
        private readonly ApresentacaoService $apresentacaoService
    ) {}

    /*
    public function registrarComJustificativa(string $matricula, int $idJustificativa): void
    {
        $local = $this->repository->registrarComJustificativa($matricula, $idJustificativa);

        $this->dispatcher->despachar(new ProntidaoRegistradaEvent(
            matricula: $matricula,
            supervisao: $local['supervisao'],
            local: $local['local'],
        ));
    }
    */

    public function obterApresentacao(int $idApresentacao): Apresentacao
    {
        return $this->apresentacaoService->obterApresentacaoPorId($idApresentacao);
    }

    public function obterPorId(int $idProntidao): Prontidao
    {
        return $this->prontidaoRepository->buscarPorId($idProntidao);
    }

    public function obterProntidoes(): array
    {
        return $this->prontidaoRepository->listarTodos();
    }

    public function verificarTempoProntidaoAtraso(\DateTimeImmutable $dataHoraApresentacao): bool
    {
        $agora = new \DateTimeImmutable();
        $intervalo = $dataHoraApresentacao->diff($agora);
        $minutos =
            ($intervalo->days * 24 * 60)
            + ($intervalo->h * 60)
            + $intervalo->i;
            
        return $minutos > 15;
    }

    public function registrarProntidao(int $idApresentacao, ?int $idJustificativa): void
    {
        $apresentacao = $this->obterApresentacao($idApresentacao);
        $status = $this->verificarTempoProntidaoAtraso($apresentacao->dataHoraApresentacao) 
            ? StatusProntidao::PRONTO_COM_ATRASO_JUSTIFICADO
            : StatusProntidao::PRONTO;
        $this->prontidaoRepository->registrar($idApresentacao, $idJustificativa, $status->value);
    }
}