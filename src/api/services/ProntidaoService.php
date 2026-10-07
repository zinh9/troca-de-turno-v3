<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Apresentacao;
use TrocaDeTurno\Entities\Prontidao;
use TrocaDeTurno\Enums\FaseProntidao;
use TrocaDeTurno\Enums\IntervaloLanche;
use TrocaDeTurno\Repositories\ApresentacaoRepository;
use TrocaDeTurno\Repositories\LancheRepository;
use TrocaDeTurno\Repositories\LocalRepository;
use TrocaDeTurno\Repositories\ProntidaoRepository;

/**
 * ProntidaoService — fluxo do botão verde "Prontidão".
 * Regras de tempo: RegraProntidao. Regra da metade do lanche: RegraLanche.
 */
final class ProntidaoService
{
    public function __construct(
        private readonly ProntidaoRepository $prontidaoRepository,
        private readonly ApresentacaoRepository $apresentacaoRepository,
        private readonly LancheRepository $lancheRepository,
        private readonly LocalRepository $localRepository,
        private readonly EventoPublisherInterface $eventoPublisher,
    ) {}

    public function obterPorId(int $idProntidao): ?Prontidao
    {
        return $this->prontidaoRepository->buscarPorId($idProntidao);
    }

    public function obterProntidoes(): array
    {
        return $this->prontidaoRepository->listarTodos();
    }

    /**
     * TODO(Zenzo): API "prontos" (liberado para a atividade?).
     * Quando existir, consulte-a aqui e devolva false se o empregado ainda NÃO
     * estiver liberado. Enquanto isso, todo mundo é liberado.
     */
    public function estaLiberadoParaAtividade(int $idEmpregado): bool
    {
        return true;
    }

    /**
     * Clique no botão verde.
     *
     * Devolve um array com 'status':
     *   'OK'                      -> gravou (vem 'prontidaoStatus', 'lancheEscolha', 'lancheForcado')
     *   'PRECISA_JUSTIFICATIVA'   -> passou de 15min; front abre select e chama de novo com idJustificativa
     *   'PRECISA_ESCOLHA_LANCHE'  -> ainda há vaga em CEDO; front pergunta manhã/tarde e chama de novo com escolhaLanche
     *   'BLOQUEADO'               -> API "prontos" ainda não liberou
     * Nos três últimos NADA é gravado (tudo-ou-nada).
     *
     * @return array<string, mixed>
     */
    public function registrarProntidao(
        int $idApresentacao,
        ?int $idJustificativa = null,
        ?string $escolhaLanche = null,
        ?\DateTimeImmutable $agora = null,
    ): array {
        $agora ??= new \DateTimeImmutable();

        $apresentacao = $this->apresentacaoRepository->buscarPorId($idApresentacao)
            ?? throw new \InvalidArgumentException("Apresentação $idApresentacao não encontrada.");

        $existente = $this->prontidaoRepository->buscarPorIdApresentacao($idApresentacao);
        if ($existente?->dataHoraProntidao !== null) {
            throw new \RuntimeException('Prontidão já registrada.');
        }

        if (!$this->estaLiberadoParaAtividade($apresentacao->idEmpregado)) {
            return ['status' => 'BLOQUEADO'];
        }

        // ---- Tempo ----
        $fase = RegraProntidao::fase($apresentacao->dataHoraApresentacao, $agora);

        if (!RegraProntidao::podeRegistrar($fase)) {
            throw new \RuntimeException('Aguarde: a prontidão libera 5 minutos após a apresentação.');
        }

        if (RegraProntidao::exigeJustificativa($fase) && $idJustificativa === null) {
            return ['status' => 'PRECISA_JUSTIFICATIVA'];
        }

        // ---- Lanche (metade da equipe pode escolher CEDO) ----
        $lancheExistente = $this->lancheRepository->buscarPorIdApresentacao($idApresentacao);
        $escolhaFinal = null;
        $forcado = false;

        if ($lancheExistente === null) {
            $total = $this->apresentacaoRepository->contarAtivasPorLocal($apresentacao->idLocal);
            $jaCedo = $this->lancheRepository->contarEscolhasAtivasPorLocal(
                $apresentacao->idLocal,
                IntervaloLanche::CEDO->value,
            );

            $pedida = $escolhaLanche !== null ? IntervaloLanche::tryFrom(strtoupper($escolhaLanche)) : null;

            if ($pedida === null && RegraLanche::cedoDisponivel($total, $jaCedo)) {
                return [
                    'status' => 'PRECISA_ESCOLHA_LANCHE',
                    'opcoes' => array_map(
                        fn(IntervaloLanche $i) => ['valor' => $i->value, 'janela' => $i->janela()],
                        IntervaloLanche::cases(),
                    ),
                ];
            }

            ['escolha' => $escolhaFinal, 'forcado' => $forcado] = RegraLanche::resolver($pedida, $total, $jaCedo);
        }

        // ---- Grava ----
        // TODO(Zenzo): dois cliques simultâneos podem ultrapassar a "metade". Para
        // blindar, envolver contagem + INSERT em transação com UPDLOCK.
        $status = RegraProntidao::statusAoRegistrar($fase);
        $this->prontidaoRepository->registrar($idApresentacao, $idJustificativa, $status->value);

        if ($escolhaFinal !== null) {
            $this->lancheRepository->registrarEscolha($idApresentacao, $escolhaFinal->value);
        }

        $this->publicar($apresentacao);

        return [
            'status' => 'OK',
            'prontidaoStatus' => $status->value,
            'lancheEscolha' => $escolhaFinal?->value ?? $lancheExistente?->escolhaIntervaloLanche,
            'lancheForcado' => $forcado,
        ];
    }

    /** CCP clicou "ACIONAR VIA RÁDIO" (só vale depois dos 15 min e sem prontidão marcada). */
    public function acionarChamadaRadio(int $idApresentacao, ?\DateTimeImmutable $agora = null): void
    {
        $agora ??= new \DateTimeImmutable();

        $apresentacao = $this->apresentacaoRepository->buscarPorId($idApresentacao)
            ?? throw new \InvalidArgumentException("Apresentação $idApresentacao não encontrada.");

        $existente = $this->prontidaoRepository->buscarPorIdApresentacao($idApresentacao);
        if ($existente?->dataHoraProntidao !== null) {
            throw new \RuntimeException('O empregado já marcou a prontidão.');
        }

        if (RegraProntidao::fase($apresentacao->dataHoraApresentacao, $agora) !== FaseProntidao::ATRASADO_JUSTIFICAR) {
            throw new \RuntimeException('A chamada via rádio só é permitida após 15 minutos da apresentação.');
        }

        $this->prontidaoRepository->registrarChamadaCpt($idApresentacao);
        $this->publicar($apresentacao);
    }

    private function publicar(Apresentacao $apresentacao): void
    {
        $local = $this->localRepository->buscarPorId($apresentacao->idLocal);
        if ($local) {
            $this->eventoPublisher->publicar($local->idSupervisao, $apresentacao->idLocal);
        }
    }
}
