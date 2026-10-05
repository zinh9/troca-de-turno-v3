import { humanizarEnum } from '../utils/formatters.js';

/**
 * StatusBadge — pequeno componente "puro": recebe estado, devolve um nó
 * DOM. Nunca lê rede, nunca guarda estado próprio além do que recebe.
 *
 * Padrão de todos os components deste projeto: `render(state) -> HTMLElement`.
 *
 * @param {{status: string, cor: 'ok'|'alerta'|'critico'|'ambar'|'info'|'neutro', textoCustom?: string}} state
 */
export function renderStatusBadge(state) {
  const el = document.createElement('span');
  el.className = `badge badge--${state.cor}`;
  el.textContent = state.textoCustom ?? humanizarEnum(state.status);
  el.setAttribute('role', 'status');
  return el;
}
