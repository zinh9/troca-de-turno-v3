<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Apresentacao;
use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Enums\StatusApresentacao;
use TrocaDeTurno\Repositories\ApresentacaoRepository;
use TrocaDeTurno\Repositories\LocalRepository;
use TrocaDeTurno\Repositories\TurnoRepository;

/**
 * ApresentacaoService — o "gerente" da apresentação.
 * Ele NÃO decide regras de tempo (isso é da RegraApresentacao); ele só:
 *   1) busca os dados, 2) pergunta às regras, 3) grava, 4) AVISA o SSE (publisher).
 */
final class ApresentacaoService
{
    public function __construct(
        private readonly ApresentacaoRepository $apresentacaoRepository,
        private readonly LocalRepository $localRepository,
        private readonly HorarioReferenciaService $horarioReferenciaService,
        private readonly EmpregadoService $empregadoService,
        private readonly TurnoRepository $turnoRepository,
        private readonly EventoPublisherInterface $eventoPublisher,
    ) {}

    public function obterEmpregado(string $matricula): Empregado
    {
        $empregado = $this->empregadoService->obterEmpregadoPorMatricula($matricula);

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

    public function jaApresentouHoje(int $idEmpregado): bool
    {
        return $this->apresentacaoRepository->buscarApresentacaoHojePorIdEmpregado($idEmpregado) !== null;
    }

    /**
     * Fluxo do botão "Apresentar" no totem.
     *
     * Devolve SEMPRE um array com 'status':
     *   'PRECISA_CONFIRMACAO' -> nada foi gravado. O front mostra um "Tem certeza?"
     *                            e chama de novo com confirmarSupervisao/confirmarTurno = true.
     *   'OK'                  -> gravou. Vem 'atrasado' (true => front mostra o select de justificativa).
     *
     * Erros de verdade (matrícula inexistente, já apresentou) viram exceção.
     *
     * @return array<string, mixed>
     */
    public function registrarApresentacao(
        string $matricula,
        int $idLocal,
        bool $confirmouSupervisao = false,
        bool $confirmouTurno = false,
        ?\DateTimeImmutable $agora = null,
    ): array {
        $agora ??= new \DateTimeImmutable();

        $empregado = $this->obterEmpregado($matricula);
        $local = $this->localRepository->buscarPorId($idLocal)
            ?? throw new \InvalidArgumentException("Local $idLocal não encontrado.");

        if ($this->jaApresentouHoje($empregado->idEmpregado)) {
            throw new \RuntimeException('Empregado já realizou apresentação neste turno.');
        }

        // ---- 1) Conferências que pedem "confirmação" (o empregado só clica OK) ----
        $supervisaoDiferente = RegraApresentacao::supervisaoDiferente($local->idSupervisao, $empregado->idSupervisao);
        $turnoAgora = RegraApresentacao::turnoPelaHora($agora);
        $turnoDiferente = RegraApresentacao::turnoDiferente($empregado->turno, $agora);

        $pendentes = [];
        if ($supervisaoDiferente && !$confirmouSupervisao) {
            $pendentes[] = [
                'tipo' => 'SUPERVISAO_DIFERENTE',
                'mensagem' => 'Você está se apresentando em uma supervisão diferente do seu cadastro.',
            ];
        }
        if ($turnoDiferente && !$confirmouTurno) {
            $pendentes[] = [
                'tipo' => 'TURNO_DIFERENTE',
                'mensagem' => "Você está se apresentando no turno $turnoAgora, diferente do seu cadastro ({$empregado->turno}). "
                    . 'Ao confirmar, seu turno será atualizado.',
            ];
        }

        if ($pendentes !== []) {
            return ['status' => 'PRECISA_CONFIRMACAO', 'confirmacoes' => $pendentes];
        }

        // ---- 2) Turno diferente confirmado: atualiza o cadastro do empregado ----
        $idTurnoEfetivo = $empregado->idTurno;
        $turnoAtualizado = false;

        if ($turnoDiferente) {
            $novoTurno = $this->turnoRepository->buscarPorNome($turnoAgora)
                ?? throw new \RuntimeException("Turno $turnoAgora não existe na tabela turno.");

            // TODO(Zenzo): idealmente UPDATE do turno + INSERT da apresentação na MESMA
            // transação (se o INSERT falhar, o turno não deveria ter mudado).
            $this->empregadoService->atualizarTurnoEmpregado($empregado->idEmpregado, $novoTurno->idTurno);
            $idTurnoEfetivo = $novoTurno->idTurno;
            $turnoAtualizado = true;
        }

        // ---- 3) Atrasado? (usa o turno EFETIVO, já atualizado) ----
        $atrasado = $this->horarioReferenciaService->estaAtrasadoChegada($idLocal, $idTurnoEfetivo);
        $status = $atrasado ? StatusApresentacao::APRESENTADO_ATRASADO : StatusApresentacao::APRESENTADO;

        $idApresentacao = $this->apresentacaoRepository->registrar($empregado->idEmpregado, $idLocal, $status->value);

        // ---- 4) AVISA o SSE: "a tela dessa supervisão/local mudou, vá buscar o painel" ----
        // Publica na supervisão ONDE ELE SE APRESENTOU (a do local), não a do cadastro.
        $this->eventoPublisher->publicar($local->idSupervisao, $idLocal);

        return [
            'status' => 'OK',
            'idApresentacao' => $idApresentacao,
            'atrasado' => $atrasado,
            'turnoAtualizado' => $turnoAtualizado,
        ];
    }

    /** Select de justificativa da coluna "apresentação" (só para quem chegou atrasado). */
    public function justificarAtraso(int $idApresentacao, int $idJustificativa): void
    {
        $apresentacao = $this->obterApresentacaoPorId($idApresentacao)
            ?? throw new \InvalidArgumentException("Apresentação $idApresentacao não encontrada.");

        if ($apresentacao->status !== StatusApresentacao::APRESENTADO_ATRASADO->value) {
            throw new \RuntimeException('Esta apresentação não está pendente de justificativa.');
        }

        $this->apresentacaoRepository->atualizarStatus(
            $idApresentacao,
            StatusApresentacao::APRESENTADO_COM_JUSTIFICATIVA->value,
            $idJustificativa,
        );

        $local = $this->localRepository->buscarPorId($apresentacao->idLocal);
        if ($local) {
            $this->eventoPublisher->publicar($local->idSupervisao, $apresentacao->idLocal);
        }
    }
}
