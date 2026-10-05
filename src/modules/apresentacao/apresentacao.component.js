import { abrirModal } from '../../shared/components/modal.component.js';
import { renderAppHeader, renderRelogioAoVivo } from '../../shared/components/app-header.component.js';
import { renderTabelaJornada } from '../../shared/components/tabela-jornada.component.js';
import { ICONES } from '../../shared/utils/icons.js';
import { router } from '../../core/router.js';

/**
 * apresentacao.component.js — tela de PÁTIO (totem), réplica do layout
 * em produção: marca + título no topo, guarita/torre + relógio ao vivo,
 * controles de apresentação, e a tabela densa de acompanhamento.
 *
 * Esta tela compõe visualmente as 5 etapas da jornada numa única tabela
 * (ver shared/components/tabela-jornada.component.js) porque é assim que
 * o sistema real funciona: uma linha do tempo por empregado, não uma
 * tela por etapa — não há mais rotas separadas de prontidão/lanche/
 * refeição/fim de jornada (ver README, seção 2).
 *
 * O contexto (supervisão + local) vem da querystring da rota, ex.:
 * `#/apresentacao?supervisao=VPN&local=Guarita_2` — é assim que cada
 * totem físico fica fixado na sua guarita (ver modules/menu, que gera
 * esses links). Sem querystring, cai num contexto de exemplo.
 */
export function montarTelaApresentacao(outlet, di, params) {
  const apresentacaoService = di.resolve('apresentacaoService');
  const timeService = di.resolve('timeService');

  const contexto = {
    supervisao: params?.get('supervisao') ?? 'VPN',
    local: params?.get('local') ?? 'Guarita_2',
  };

  outlet.dataset.contexto = 'totem';
  outlet.innerHTML = '';

  const tela = document.createElement('div');
  tela.className = 'tela-apresentacao';
  outlet.appendChild(tela);

  let intervaloPolling = null;

  async function carregar() {
    try {
      const { emManutencao, info, empregados } = await apresentacaoService.buscarPainel(contexto);

      if (emManutencao) {
        renderManutencao(tela);
        return;
      }

      timeService.sincronizarComServidor(info.serverTime);
      renderPainel(tela, { info, empregados, timeService, contexto, apresentacaoService });
    } catch (erro) {
      abrirModal({
        tipo: 'erro',
        titulo: 'Erro de sistema',
        mensagem: erro.message ?? 'Não foi possível carregar o painel do pátio.',
        acaoRotulo: 'Tentar novamente',
        aoAcionar: carregar,
      });
    }
  }

  carregar();
  intervaloPolling = setInterval(carregar, 40_000);

  return function desmontar() {
    clearInterval(intervaloPolling);
  };
}

function renderManutencao(tela) {
  tela.innerHTML = '';
  abrirModal({
    tipo: 'manutencao',
    titulo: 'Sistema em manutenção',
    mensagem: 'O painel de troca de turno está temporariamente indisponível. Tente novamente em instantes.',
  });
}

function renderPainel(tela, { info, empregados, timeService, contexto, apresentacaoService }) {
  tela.innerHTML = '';

  tela.appendChild(renderAppHeader({ variante: 'patio', ultimaAtualizacaoIso: info.ultimaAtualizacao, timeService }));

  const controles = document.createElement('div');
  controles.className = 'tela-apresentacao__controles';
  controles.innerHTML = `
    <div class="tela-apresentacao__guarita">
      <span class="tela-apresentacao__guarita-barra"></span>
      ${ICONES.torre}
      <strong>${contexto.supervisao} - ${contexto.local.replace('_', ' ')}</strong>
    </div>
  `;
  controles.appendChild(renderRelogioAoVivo(timeService));
  tela.appendChild(controles);

  const formularioLinha = document.createElement('div');
  formularioLinha.className = 'tela-apresentacao__form-linha';

  const formulario = document.createElement('form');
  formulario.className = 'tela-apresentacao__form';
  formulario.innerHTML = `
    <input type="text" name="matricula" placeholder="Digite sua Matrícula" autocomplete="off" inputmode="numeric" />
    <button type="submit" class="botao-acao-azul">&check; Apresentar</button>
    <button type="button" class="botao-desabilitado">${ICONES.escudo} Assinar DSS</button>
  `;
  formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    const matricula = new FormData(formulario).get('matricula')?.toString().trim();
    if (!matricula) return;
    await apresentacaoService.apresentar(matricula);
    formulario.reset();
  });

  const acoesTopo = document.createElement('div');
  acoesTopo.className = 'tela-apresentacao__acoes-topo';
  acoesTopo.innerHTML = `
    <button class="tela-apresentacao__alerta" title="Avisos do sistema">${ICONES.alerta}</button>
    <button class="tela-apresentacao__menu">${ICONES.menu} Menu</button>
  `;
  acoesTopo.querySelector('.tela-apresentacao__menu').addEventListener('click', () => router.navegar('/menu'));

  formularioLinha.append(formulario, acoesTopo);
  tela.appendChild(formularioLinha);

  const acoes = {
    aoEnviarJustificativaProntidao: (matricula, motivo) =>
      apresentacaoService.apiService?.enviarJustificativa?.('prontidao', matricula, motivo),
    aoAcionarLanche: (matricula) => apresentacaoService.apiService?.iniciarIntervalo?.('lanche', matricula),
    aoEnviarJustificativaLanche: (matricula, motivo) =>
      apresentacaoService.apiService?.enviarJustificativa?.('lanche', matricula, motivo),
    aoAcionarRefeicao: (matricula) => apresentacaoService.apiService?.iniciarIntervalo?.('refeicao', matricula),
    aoFinalizarJornada: (matricula) => apresentacaoService.apiService?.finalizarJornada?.(matricula),
  };

  tela.appendChild(renderTabelaJornada(empregados, { variante: 'totem', timeService, acoes }));
}
