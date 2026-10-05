/**
 * icons.js — conjunto mínimo de ícones inline (SVG como string), sem
 * dependência de icon font. Todos usam `currentColor` para herdar a cor
 * do elemento pai, então basta trocar a cor via CSS.
 *
 * Uso: elemento.innerHTML = ICONES.relogio
 *      ou: elemento.insertAdjacentHTML('afterbegin', ICONES.pessoa)
 */
export const ICONES = {
  pessoa: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c1.4-4 4-6 7.5-6s6.1 2 7.5 6"/></svg>`,

  relogio: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>`,

  sanduiche: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11h16v2a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-2Z"/><path d="M5 11c0-3 3-6 7-6s7 3 7 6"/><path d="M4 16h16"/></svg>`,

  garfoFaca: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3v7a2 2 0 0 0 2 2v9"/><path d="M7 3v5M10 3v5"/><path d="M16 3c-1.4 1-2 2.6-2 5s.6 4 2 5v8"/></svg>`,

  menu: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>`,

  alerta: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4"/><circle cx="12" cy="17" r=".6" fill="currentColor" stroke="none"/></svg>`,

  escudo: `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 5 6v5c0 4.5 3 7.7 7 10 4-2.3 7-5.5 7-10V6l-7-3Z"/></svg>`,

  reenviar: `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.6 6.3"/><path d="M3 17v-5h5"/></svg>`,

  enviar: `<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M3 11.5 20.5 4 13 21.5l-2.3-6.7L3 11.5Z"/></svg>`,

  radio: `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="9" rx="1.5"/><path d="M8 10 16 4"/><circle cx="9" cy="14.5" r="1.4" fill="currentColor" stroke="none"/><path d="M13 13.5h4M13 16h4"/></svg>`,

  torre: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="3" width="12" height="18" rx="1"/><path d="M9 3v18M14 3v18M6 8h12M6 13h12"/></svg>`,

  chevronBaixo: `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>`,

  trem: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="5" width="16" height="11" rx="2.5"/><path d="M4 11h16"/><circle cx="8" cy="19" r="1.4" fill="currentColor" stroke="none"/><circle cx="16" cy="19" r="1.4" fill="currentColor" stroke="none"/></svg>`,
};

/** Ponto de status circular (usado nos dois indicadores ao lado do nome). */
export function dotStatus(ativo) {
  return `<span class="dot-status ${ativo ? 'dot-status--ok' : 'dot-status--pendente'}"></span>`;
}
