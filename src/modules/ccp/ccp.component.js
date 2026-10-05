import { abrirModal } from '../../shared/components/modal.component.js';
import { renderAppHeader, renderRelogioAoVivo } from '../../shared/components/app-header.component.js';
import { renderTabelaJornada } from '../../shared/components/tabela-jornada.component.js';
import { ICONES } from '../../shared/utils/icons.js';
import { router } from '../../core/router.js';

/**
 * ccp.component.js — tela de supervisão (CCP), réplica do layout em
 * produção: título em vermelho, trilho ferroviário animado, e a mesma
 * tabela densa da tela de pátio — só que com colunas extras (Cargo,
 * Local, Chamada) e ações que o supervisor executa em nome do empregado.
 *
 * Diferente do totem (um link por guarita), o CCP é **um link único por
 * supervisão** — o CPT enxerga todas as guaritas daquela supervisão na
 * mesma tabela, sem seletor: `#/ccp?supervisao=VPN` (ver modules/menu,
 * que gera esse link). Sem querystring, cai num contexto de exemplo.
 */
export function montarTelaCcp(outlet, di, params) {
  const ccpService = di.resolve('ccpService');
  const timeService = di.resolve('timeService');

  const supervisao = params?.get('supervisao') ?? 'VPN';

  outlet.dataset.contexto = 'painel';
  outlet.innerHTML = '';

  const tela = document.createElement('div');
  tela.className = 'tela-ccp';
  outlet.appendChild(tela);

  let intervaloPolling = null;

  async function carregar() {
    try {
      // O CCP não filtra por local — a API deve devolver todas as
      // guaritas da supervisão quando `local` não é informado.
      const contexto = { supervisao };
      const { emManutencao, info, empregados } = await ccpService.buscarPainel(contexto);

      if (emManutencao) {
        tela.innerHTML = '';
        abrirModal({
          tipo: 'manutencao',
          titulo: 'Sistema em manutenção',
          mensagem: 'O painel de supervisão está temporariamente indisponível.',
        });
        return;
      }

      timeService.sincronizarComServidor(info.serverTime);
      renderPainel(tela, { info, empregados, timeService, ccpService, supervisao });
    } catch (erro) {
      abrirModal({
        tipo: 'erro',
        titulo: 'Erro de sistema',
        mensagem: erro.message ?? 'Não foi possível carregar o painel de supervisão.',
        acaoRotulo: 'Tentar novamente',
        aoAcionar: carregar,
      });
    }
  }

  carregar();
  intervaloPolling = setInterval(carregar, 15_000);

  return function desmontar() {
    clearInterval(intervaloPolling);
  };
}

function renderPainel(tela, { info, empregados, timeService, ccpService, supervisao }) {
  tela.innerHTML = '';

  tela.appendChild(renderAppHeader({ variante: 'ccp', ultimaAtualizacaoIso: info.ultimaAtualizacao, timeService }));

  const controles = document.createElement('div');
  controles.className = 'tela-ccp__controles';
  controles.innerHTML = `
    <div class="tela-ccp__supervisao">
      ${ICONES.trem}
      <strong>${supervisao.replace('_', ' ')}</strong>
    </div>
  `;
  const botaoMenu = document.createElement('button');
  botaoMenu.className = 'tela-ccp__menu';
  botaoMenu.innerHTML = `${ICONES.menu} Menu`;
  botaoMenu.addEventListener('click', () => router.navegar('/menu'));

  controles.append(renderRelogioAoVivo(timeService), botaoMenu);
  tela.appendChild(controles);

  const acoes = {
    aoEnviarJustificativaProntidao: (matricula, motivo) =>
      ccpService.apiService?.enviarJustificativa?.('prontidao', matricula, motivo),
    aoAcionarLanche: (matricula) => ccpService.liberarLanche(matricula),
    aoEnviarJustificativaLanche: (matricula, motivo) => ccpService.apiService?.enviarJustificativa?.('lanche', matricula, motivo),
    aoAcionarRefeicao: (matricula) => ccpService.liberarRefeicao(matricula),
    aoAcionarRadio: (matricula) => ccpService.acionarChamadaRadio(matricula),
    aoFinalizarJornada: (matricula) => ccpService.apiService?.finalizarJornada?.(matricula),
  };

  tela.appendChild(renderTabelaJornada(empregados, { variante: 'ccp', timeService, acoes }));
}
