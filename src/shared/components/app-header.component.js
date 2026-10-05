import { ICONES } from '../utils/icons.js';
import { formatarData, formatarHora } from '../utils/formatters.js';

/**
 * app-header.component.js — barra de topo compartilhada pelas telas de
 * Pátio (totem) e CCP. Muda só o rótulo/cor do título e a barra
 * decorativa abaixo dele (linha sólida âmbar no pátio; trilho + trem
 * animado no CCP, remetendo à operação ferroviária).
 *
 * @param {{
 *   variante: 'patio'|'ccp',
 *   ultimaAtualizacaoIso: string,
 *   timeService: import('../services/time.service.js').TimeService,
 * }} opcoes
 */
export function renderAppHeader({ variante, ultimaAtualizacaoIso, timeService }) {
  const header = document.createElement('header');
  header.className = `app-header app-header--${variante}`;

  const tituloPrincipal = variante === 'ccp' ? 'CCP' : 'PÁTIO';

  header.innerHTML = `
    <div class="app-header__topo">
      <div class="app-header__marca">
        <span>VALE</span>
      </div>
      <h1 class="app-header__titulo">
        <span class="app-header__titulo-destaque">${tituloPrincipal}</span> | SISTEMA TROCA DE TURNO
      </h1>
      <div class="app-header__atualizacao">
        <span class="texto-terciario">Última Atualização:</span>
        <strong id="app-header-data"></strong>
      </div>
    </div>
    <div class="app-header__barra">${variante === 'ccp' ? renderTrilhoAnimado() : ''}</div>
  `;

  const elData = header.querySelector('#app-header-data');
  elData.textContent = `${formatarData(ultimaAtualizacaoIso)} ${formatarHora(ultimaAtualizacaoIso)}`;

  return header;
}

function renderTrilhoAnimado() {
  return `
    <div class="app-header__trilho">
      <div class="app-header__trilho-progresso"><span class="app-header__trilho-trem">${ICONES.trem}</span></div>
    </div>
  `;
}

/** Relógio ao vivo grande, usado abaixo do header nas duas telas. */
export function renderRelogioAoVivo(timeService) {
  const el = document.createElement('div');
  el.className = 'relogio-ao-vivo fonte-mono';

  function atualizar(agora) {
    el.textContent = agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }
  atualizar(timeService.agora());
  timeService.onTick(atualizar);
  return el;
}
