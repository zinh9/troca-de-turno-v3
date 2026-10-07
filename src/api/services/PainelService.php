<?php

declare(strict_types=1);

namespace TrocaDeTurno\Services;

use TrocaDeTurno\Entities\Empregado;
use TrocaDeTurno\Entities\Lanche;
use TrocaDeTurno\Entities\Prontidao;
use TrocaDeTurno\Enums\StatusLanche;
use TrocaDeTurno\Repositories\ApresentacaoRepository;
use TrocaDeTurno\Repositories\EmpregadoRepository;
use TrocaDeTurno\Repositories\LancheRepository;
use TrocaDeTurno\Repositories\ProntidaoRepository;

final class PainelService
{
    public function __construct(
        private readonly EmpregadoRepository $empregadoRepository,
        private readonly ApresentacaoRepository $apresentacaoRepository,
        private readonly ProntidaoRepository $prontidaoRepository,
        //private readonly LancheRepository $lancheRepository
    )
    {}

    /** @return array{empregados: array[]} */
    public function montarPainel(int $idSupervisao, int $idLocal): array
    {
        $empregados = $this->empregadoRepository->listarPorSupervisaoELocal(idSupervisao: $idSupervisao, idLocal: $idLocal);

        $empregadosMontados = array_map(
            fn($empregado) => $this->montarEmpregado($empregado),
            $empregados
        );

        return ['empregados' => $empregadosMontados];
    }

    private function montarEmpregado(Empregado $empregado): array
    {
        $apresentacao = $this->apresentacaoRepository->buscarApresentacaoHojePorIdEmpregado($empregado->idEmpregado);

        if (!$apresentacao) {
            return [
                'nome' => $empregado->nome,
                'matricula' => $empregado->matricula,
                'cargo' => $empregado->cargo,
                'turno' => $empregado->turno,
                'jornada' => [
                    'apresentacao' => ['dataHora' => null, 'status' => 'JUSTIFICAR', 'justificativa' => null],
                    'prontidao' => $this->prontidaoVazia(),
                    'lanche' => $this->lancheVazia(),
                    'refeicao' => $this->lancheVazia(),
                    'fimJornada' => ['dataHora' => null, 'status' => null, 'justificativa' => null, 'fimJornadaCpt' => null]
                ],
            ];
        }

        $prontidao = $this->prontidaoRepository->buscarPorIdApresentacao(idApresentacao: $apresentacao->idApresentacao);
        // $lanche = $this->lancheRepository->buscarPorIdApresentacao(idApresentacao: $apresentacao->idApresentacao);

        return [
            'nome' => $empregado->nome,
            'matricula' => $empregado->matricula,
            'cargo' => $empregado->cargo,
            'turno' => $empregado->turno,
            'jornada' => [
                'apresentacao' => [
                    'dataHora' => $apresentacao->dataHoraApresentacao->format('Y-m-d\TH:i:s'),
                    'status' => $apresentacao->status,
                    'justificativa' => $apresentacao->idJustificativa
                ],
                'prontidao' => $prontidao ? $this->mapearProntidao($prontidao) : $this->prontidaoVazia(),
                'lanche' => $this->lancheVazia(),
                'refeiaco' => $this->lancheVazia(),
                'fimJornada' => ['dataHora' => null, 'status' => null, 'justificativa' => null, 'fimJornadaCpt' => null]
            ],
        ];
    }

    private function mapearProntidao(Prontidao $prontidao): array
    {
        return [
            'dataHora' => $prontidao->dataHoraProntidao->format('Y-m-d\TH:i:s'),
            'chamadaCpt' => $prontidao->dataHoraChamadaCpt->format('Y-m-d\TH:i:s'),
            'status' => $prontidao->status,
            'justificativa' => $prontidao->idJustificativa
        ];
    }

    private function prontidaoVazia(): array
    {
        return [
            'dataHora' => null,
            'chamadaCpt' => null,
            'status' => 'AGUARDANDO',
            'justificativa' => null
        ];
    }

    private function mapearLanche(Lanche $lanche): array
    {
        $status = match (true) {
            $lanche->dataHoraProntidaoLanche !== null => StatusLanche::CONCLUIDO,
            $lanche->dataHoraLanchePatio !== null => StatusLanche::EM_ANDAMENTO,
            $lanche->escolhaIntervaloLanche !== null => StatusLanche::AGUARDANDO_JANELA,
            default => StatusLanche::AGUARDANDO_ESCOLHA,
        };

        return [
            'intervaloEscolhido' => $lanche->escolhaIntervaloLanche,
            'janela' => null,
            'status' => $status,
            'dataHoraInicio' => ($lanche->dataHoraLanchePatio ?? $lanche->dataHoraLancheCpt)?->format('Y-m-d\TH:i:s'),
            'dataHoraProntidao' => $lanche->dataHoraProntidaoLanche?->format('Y-m-d\TH:i:s'),
            'justificativa' => $lanche->idJustificativaInicio,
            'liberadoCpt' => $lanche->dataHoraLancheCpt !== null
        ];
    }

    private function lancheVazia() : array 
    {
        return [
            'intervaloEscolhido' => null,
            'janela' => null,
            'status' => null,
            'dataHoraInicio' => null,
            'dataHoraProntidao' => null,
            'justificativa' => null,
            'liberadoCpt' => null
        ];
    }
}