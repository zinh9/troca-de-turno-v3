<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Apresentacao;
use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Entities\Lanche;
use TrocaDeTurno\Entities\Prontidao;
use TrocaDeTurno\Enums\FaseProntidao;
use TrocaDeTurno\Enums\IntervaloLanche;
use TrocaDeTurno\Enums\StatusApresentacao;
use TrocaDeTurno\Enums\StatusLanche;
use TrocaDeTurno\Repositories\ApresentacaoRepository;
use TrocaDeTurno\Repositories\EmpregadoRepository;
use TrocaDeTurno\Repositories\LancheRepository;
use TrocaDeTurno\Repositories\LocalRepository;
use TrocaDeTurno\Repositories\ProntidaoRepository;

/**
 * PainelService — o "tradutor": junta Apresentacao + Prontidao + Lanche + Empregado
 * e monta o JSON no formato que o FRONT espera (contrato do README do front).
 *
 * Mudança importante: a tabela agora mostra quem SE APRESENTOU nas últimas 13h
 * naquele local (não mais "todos os empregados cadastrados da supervisão").
 * Assim, quem se apresenta numa supervisão diferente da dele também aparece.
 */
final class PainelService
{
    private const FORMATO = 'Y-m-d\TH:i:s';

    public function __construct(
        private readonly EmpregadoRepository $empregadoRepository,
        private readonly ApresentacaoRepository $apresentacaoRepository,
        private readonly ProntidaoRepository $prontidaoRepository,
        private readonly LancheRepository $lancheRepository,
        private readonly LocalRepository $localRepository,
    ) {}

    /**
     * @param ?int $idLocal null = CCP (todos os locais da supervisão)
     * @return array{empregados: array[]}
     */
    public function montarPainel(int $idSupervisao, ?int $idLocal, ?\DateTimeImmutable $agora = null): array
    {
        $agora ??= new \DateTimeImmutable();

        $nomesLocais = [];
        foreach ($this->localRepository->listarTodos() as $local) {
            $nomesLocais[$local->idLocal] = $local->local;
        }

        $empregados = [];
        foreach ($this->apresentacaoRepository->listarAtivasPorSupervisao($idSupervisao, $idLocal) as $apresentacao) {
            $empregado = $this->empregadoRepository->buscarPorId($apresentacao->idEmpregado);
            if ($empregado === null) {
                continue;
            }
            $empregados[] = $this->montarEmpregado($empregado, $apresentacao, $nomesLocais, $agora);
        }

        return ['empregados' => $empregados];
    }

    private function montarEmpregado(Empregado $e, Apresentacao $a, array $nomesLocais, \DateTimeImmutable $agora): array
    {
        $prontidao = $this->prontidaoRepository->buscarPorIdApresentacao($a->idApresentacao);
        $lanche = $this->lancheRepository->buscarPorIdApresentacao($a->idApresentacao);

        return [
            'idApresentacao' => $a->idApresentacao,
            'matricula' => $e->matricula,   // o front mostra no tooltip do nome
            'nome' => $e->nome,
            'cargo' => $e->cargo,
            'turno' => $e->turno,
            'idLocal' => $a->idLocal,
            'local' => $nomesLocais[$a->idLocal] ?? null,
            'jornada' => [
                'apresentacao' => $this->mapearApresentacao($a),
                'prontidao' => $this->mapearProntidao($a, $prontidao, $agora),
                'lanche' => $lanche ? $this->mapearLanche($lanche) : $this->lancheVazio(),
                'refeicao' => $this->lancheVazio(),   // TODO(Zenzo): próxima etapa
                'fimJornada' => ['dataHora' => null, 'status' => null, 'justificativa' => null, 'fimJornadaCpt' => null],
            ],
        ];
    }

    /** Traduz o status do SEU enum para o vocabulário do front (OK | JUSTIFICAR | JUSTIFICATIVA_OK). */
    private function mapearApresentacao(Apresentacao $a): array
    {
        $status = match ($a->status) {
            StatusApresentacao::APRESENTADO->value => 'OK',
            StatusApresentacao::APRESENTADO_ATRASADO->value => 'JUSTIFICAR',
            StatusApresentacao::APRESENTADO_COM_JUSTIFICATIVA->value => 'JUSTIFICATIVA_OK',
            default => 'OK',
        };

        return [
            'dataHora' => $a->dataHoraApresentacao->format(self::FORMATO),
            'status' => $status,
            'justificativa' => $a->idJustificativa,
        ];
    }

    private function mapearProntidao(Apresentacao $a, ?Prontidao $p, \DateTimeImmutable $agora): array
    {
        // Já marcou: não existe mais "fase", só o resultado.
        if ($p?->dataHoraProntidao !== null) {
            return [
                'dataHora' => $p->dataHoraProntidao->format(self::FORMATO),
                'status' => $p->status === 'PRONTO_COM_ATRASO_JUSTIFICADO' ? 'PRONTO_COM_ATRASO' : 'PRONTO',
                'fase' => null,
                'justificativa' => $p->idJustificativa,
                'chamadaCpt' => $p->dataHoraChamadaCpt?->format(self::FORMATO),
                'liberaEm' => null,
                'atrasaEm' => null,
                'podeAcionarRadio' => false,
            ];
        }

        // Ainda não marcou: a "fase" depende do RELÓGIO (por isso mandamos também
        // liberaEm/atrasaEm — o front liga timers e muda sozinho, sem novo request).
        $fase = RegraProntidao::fase($a->dataHoraApresentacao, $agora);
        $chamada = $p?->dataHoraChamadaCpt;

        return [
            'dataHora' => null,
            'status' => 'AGUARDANDO',
            'fase' => $fase->value,
            'justificativa' => null,
            'chamadaCpt' => $chamada?->format(self::FORMATO),
            'liberaEm' => RegraProntidao::liberaEm($a->dataHoraApresentacao)->format(self::FORMATO),
            'atrasaEm' => RegraProntidao::atrasaEm($a->dataHoraApresentacao)->format(self::FORMATO),
            // botão "ACIONAR VIA RÁDIO" do CCP
            'podeAcionarRadio' => $fase === FaseProntidao::ATRASADO_JUSTIFICAR && $chamada === null,
        ];
    }

    private function mapearLanche(Lanche $l): array
    {
        $status = match (true) {
            $l->dataHoraProntidaoLanche !== null => StatusLanche::CONCLUIDO,
            $l->dataHoraLanchePatio !== null => StatusLanche::EM_ANDAMENTO,
            $l->escolhaIntervaloLanche !== null => StatusLanche::AGUARDANDO_JANELA,
            default => StatusLanche::AGUARDANDO_ESCOLHA,
        };

        return [
            'intervaloEscolhido' => $l->escolhaIntervaloLanche,
            'janela' => $l->escolhaIntervaloLanche !== null
                ? IntervaloLanche::tryFrom($l->escolhaIntervaloLanche)?->janela()
                : null,
            'status' => $status->value,
            'dataHoraInicio' => ($l->dataHoraLanchePatio ?? $l->dataHoraLancheCpt)?->format(self::FORMATO),
            'dataHoraProntidao' => $l->dataHoraProntidaoLanche?->format(self::FORMATO),
            'justificativa' => $l->idJustificativaInicio,
            'liberadoCCP' => $l->dataHoraLancheCpt !== null,
        ];
    }

    private function lancheVazio(): array
    {
        return [
            'intervaloEscolhido' => null, 'janela' => null, 'status' => null,
            'dataHoraInicio' => null, 'dataHoraProntidao' => null,
            'justificativa' => null, 'liberadoCCP' => null,
        ];
    }
}
